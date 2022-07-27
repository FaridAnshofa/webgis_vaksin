<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Input;
use Redirect;
use Session;
use file;
use HelperData;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use DateTime;
use DatePeriod;
use DateInterval;
date_default_timezone_set('Asia/Jakarta');

class IndexController extends Controller
{
    public function viewDashboard($type=null,$val=null)
    {
    	$viewPage = "app.dashboard.index";
		$page	= ["Home","Dashboard"];
		$mDataWilayah	= DB::table('m_wilayah')->get();
		$dataNilaiBobot	= DB::table('t_bobot_wilayah')->get();
		$dataClusterWilayah = DB::table('t_bobot_wilayah as tbw')
							->join('t_bobot_wilayah_cluster as tbwc','tbw.id','=','tbwc.id_bobot')
								->get();
		$dataColor = '';
		$dataName = '';
		$dataSeriesMaps = '';
		$cluster = [];
		if ($type == "cluster") {
			$dataName = "Cluster ".$val;
			if($val == 1){
				$dataColor = " color: 'green',";
			}else{
				$dataColor = " color: 'red',";
			}
		}else if ($type == "date") {
			$dataName = $val;
		}else{
			$dataName = 'Null';
		}

		$dataCluster = DB::table('t_bobot_wilayah as tbw')
							->join('t_bobot_wilayah_cluster as tbwc','tbw.id','=','tbwc.id_bobot')
								->where('tbwc.cluster',$val)
									->get();

		$nilaiBobot = DB::table('t_bobot_wilayah')
						->where('date',$val)
							->get();
		foreach ($dataCluster as $tmpCl) {
			foreach ($mDataWilayah as $tmp) {
    			$arCode = 'ar_'.strtolower($tmp->kode_wilayah);
				$cluster['cluster'.$val][$arCode][$tmpCl->date] = $tmpCl->date.' : '.$tmpCl->$arCode;
				// $cluster['cluster'.$arCode][$arCode][$tmpCl->date]['val'] = $tmpCl->$arCode;
	    	}
		}


		$dataSeriesMaps = "series:[{";
		$dataSeriesMaps .= "type: 'map', 
							name: '".$dataName."',
					        joinBy: 'id',
					        ".$dataColor."
					        mapData: [";
		foreach ($mDataWilayah as $tmp) {
			$dataSeriesMaps .= "{
				id : '".strtolower($tmp->kode_wilayah)."',
				name : '".$tmp->nama_wilayah."',
				path : '".$tmp->path."'
			},";
		}
		$dataSeriesMaps .= "],data : [";
		// foreach ($dataCluster as $tmpCl) {
			foreach ($mDataWilayah as $tmp) {
				$arCode = 'ar_'.strtolower($tmp->kode_wilayah);
				$dataSeriesMaps .= "{
					id : '".strtolower($tmp->kode_wilayah)."',";
					if($type == "cluster"){
						$dataSeriesMaps .= "value : '";	
						foreach ($dataCluster as $tmpCl) {
							$dataSeriesMaps .= "".$cluster['cluster'.$val][$arCode][$tmpCl->date]."<br> ";
						}
					}else if($type == "date"){
						$dataSeriesMaps .= "value : '";	
						foreach ($nilaiBobot as $tmpBbt) {
							$dataSeriesMaps .= "".$tmpBbt->$arCode."<br> ";
						}
					}else{
						$dataSeriesMaps .= "value : '0";	
					}
				$dataSeriesMaps .= "'},";
			}
		// }
		$dataSeriesMaps .= "], dataLabels: {
          enabled: true,
          format: '{point.name}'
        }}]";

  //       echo '<pre>';
		// print_r($dataSeriesMaps);
  //   	echo '</pre>';

		return view($viewPage,array(
			'pageNow' 	 	=> $page,
			'mDataWilayah' 	=> $mDataWilayah,
			'dataNilaiBobot'=> $dataNilaiBobot,
			'dataSeriesMaps'=> $dataSeriesMaps,
			'dataClusterWilayah'=> $dataClusterWilayah,
			'menuActive' 	=> "dashboard"
		));
    }

    public function prosesNilaiBobot(Request $req)
    {
    	$txtCount = $req->txtCount;
		$slctWilayah = $req->slctWilayah;
		$txtJmlhPdk = (float) str_replace('.', '', $req->txtJmlhPdk);
		$txtDate = $req->txtDate;
		$nb = $txtCount / $txtJmlhPdk * 100;

		$data = DB::table('t_bobot_wilayah')
				->where('date',$txtDate)
					->get();
		$dataWilayah = DB::table('m_wilayah')
					->where('id',$slctWilayah)
						->first();

		$prosesUpdate = DB::table('t_bobot_wilayah');
		if(count($data) == 0){
			$prosesUpdate = $prosesUpdate->insert([
				'date' => $txtDate,
				'ar_'.strtolower($dataWilayah->kode_wilayah) => $nb
			]);
		}else{
			$prosesUpdate = $prosesUpdate->where('date',$txtDate)
			->update([
				'ar_'.strtolower($dataWilayah->kode_wilayah) => $nb
			]);
		}

		$this->HitungJarakCentroid();

		if ($prosesUpdate) {
			return redirect()->back()->with('message', 'Success')->with('message_status', 'success');
		} else {
			return redirect()->back()->with('message', 'Failed')->with('message_status', 'failed');
		}
    }

    public function HitungJarakCentroid()
    {
    	$mDataWilayah	= DB::table('m_wilayah')->get();
    	$dataBobot = DB::table('t_bobot_wilayah')->get();
    	$NilaiCentroid = [];
    	$NilaiCentroid = $dataBobot;
    	$HasilJarakCentroid = [];
    	// $HasilJarakCentroid2 = [];
    	$kelompokCluster = [];
    	$cluster = [];
    	$cb = [];
    	$cb2 = [];
    	$x = [];
    	$c = [];
    	$inputData = [];
    	$noArr = 0;
    	$noArr2 = 0;
    	$noArr3 = 0;
    	$arCode = '';
    	$clu = 0;
    	$sumWilayah = 0;
    	if(count($dataBobot) >= 4){
    		foreach ($dataBobot as $tmpBbt) {
		    	foreach ($mDataWilayah as $tmp) {
	    			$arCode = 'ar_'.strtolower($tmp->kode_wilayah);
					$x[$noArr]['ar_'.strtolower($tmp->kode_wilayah)] = $tmpBbt->$arCode;
					$c[$noArr]['ar_'.strtolower($tmp->kode_wilayah)] = $tmpBbt->$arCode;
		    	}
	    		$noArr++;
	    	}

	    	foreach ($dataBobot as $tmpBbt) {
		    	foreach ($mDataWilayah as $tmp) {
	    			$arCode = 'ar_'.strtolower($tmp->kode_wilayah);
					$HasilJarakCentroid[0][$noArr2][$arCode] = pow(($x[$noArr2][$arCode] - $c[0][$arCode]),2);
					$HasilJarakCentroid[1][$noArr2][$arCode] = pow(($x[$noArr2][$arCode] - $c[1][$arCode]),2);
		    	}
				$HasilJarakCentroid[0][$noArr2]['akar'] = sqrt(array_sum($HasilJarakCentroid[0][$noArr2]));
				$HasilJarakCentroid[1][$noArr2]['akar'] = sqrt(array_sum($HasilJarakCentroid[1][$noArr2]));
				foreach ($mDataWilayah as $tmp) {
	    			$arCode = 'ar_'.strtolower($tmp->kode_wilayah);
	    			if($HasilJarakCentroid[0][$noArr2]['akar'] < $HasilJarakCentroid[1][$noArr2]['akar']){
						$kelompokCluster['cluster1'][$noArr2][$arCode] = $tmpBbt->$arCode;
					}else{
						$kelompokCluster['cluster2'][$noArr2][$arCode] = $tmpBbt->$arCode;
					}
		    	}

		    	foreach ($mDataWilayah as $tmp) {
	    			$arCode = 'ar_'.strtolower($tmp->kode_wilayah);
	    			if($HasilJarakCentroid[0][$noArr2]['akar'] < $HasilJarakCentroid[1][$noArr2]['akar']){
						$cluster['c1'] = array_merge($kelompokCluster['cluster1']);
					}else{
						$cluster['c2'] = array_merge($kelompokCluster['cluster2']);
					}
		    	}
	    		$noArr2++;
	    	}

			foreach ($mDataWilayah as $tmp) 
			{
				$arCode = 'ar_'.strtolower($tmp->kode_wilayah);
				$cb['cb1'][$arCode] = 0;
	    		for($j=0;$j<count($cluster['c1']);$j++)
				{
					$cb['cb1'][$arCode] = ($cb['cb1'][$arCode] + $cluster['c1'][$j][$arCode]);  
				}
				$cb['cb1'][$arCode] = $cb['cb1'][$arCode]/count($cluster['c1']);  

				$cb['cb2'][$arCode] = 0;
	    		for($j=0;$j<count($cluster['c2']);$j++)
				{
					$cb['cb2'][$arCode] = ($cb['cb2'][$arCode] + $cluster['c2'][$j][$arCode]);  
				}
				$cb['cb2'][$arCode] = $cb['cb2'][$arCode]/count($cluster['c2']);  
			}

			$noArr = 0;
	    	$noArr2 = 0;
	    	$cbArr = 1;
	    	$cbArr2 = 1;
	    	unset($HasilJarakCentroid[0]);
			unset($HasilJarakCentroid[1]);
			foreach ($dataBobot as $tmpBbt) {
	    		foreach ($mDataWilayah as $tmp) {
					$arCode = 'ar_'.strtolower($tmp->kode_wilayah);
					$HasilJarakCentroid[0][$noArr2][$arCode] = pow(($x[$noArr2][$arCode] - $cb['cb1'][$arCode]),2);
					$HasilJarakCentroid[1][$noArr2][$arCode] = pow(($x[$noArr2][$arCode] - $cb['cb2'][$arCode]),2);
		    	}
				// $cbArr++;
				$HasilJarakCentroid[0][$noArr2]['akar_dc1'] = sqrt(array_sum($HasilJarakCentroid[0][$noArr2]));
				$HasilJarakCentroid[1][$noArr2]['akar_dc2'] = sqrt(array_sum($HasilJarakCentroid[1][$noArr2]));
				foreach ($mDataWilayah as $tmp) {
	    			$arCode = 'ar_'.strtolower($tmp->kode_wilayah);
	    			if($HasilJarakCentroid[0][$noArr2]['akar_dc1'] < $HasilJarakCentroid[1][$noArr2]['akar_dc2']){
						// $HasilJarakCentroid[0][$noArr2]['cluster'] = 1;
						$kelompokCluster['cluster1'][$noArr2][$arCode] = $tmpBbt->$arCode;
						$clu = 1;
					}else{
						// $HasilJarakCentroid[0][$noArr2]['cluster'] = 2;
						$kelompokCluster['cluster2'][$noArr2][$arCode] = $tmpBbt->$arCode;
						$clu = 2;
					}
					
		    	}
		    	$inputData[$noArr2]['id'] = $tmpBbt->id;
		    	$inputData[$noArr2]['date'] = $tmpBbt->id;
		    	$inputData[$noArr2]['dc1'] = substr($HasilJarakCentroid[0][$noArr2]['akar_dc1'], 0, 6);
		    	$inputData[$noArr2]['dc2'] = substr($HasilJarakCentroid[1][$noArr2]['akar_dc2'], 0, 6);
		    	$inputData[$noArr2]['cluster'] = $clu;

		    	foreach ($mDataWilayah as $tmp) {
	    			$arCode = 'ar_'.strtolower($tmp->kode_wilayah);
	    			if($HasilJarakCentroid[0][$noArr2]['akar_dc1'] < $HasilJarakCentroid[1][$noArr2]['akar_dc2']){
						$cluster['c1'] = array_merge($kelompokCluster['cluster1']);
					}else{
						$cluster['c2'] = array_merge($kelompokCluster['cluster2']);
					}
		    	}
	    		$noArr2++;
	    	}

	    	foreach ($mDataWilayah as $tmp) 
			{
				$arCode = 'ar_'.strtolower($tmp->kode_wilayah);
				$cb2['cb1'][$arCode] = 0;
	    		for($j=0;$j<count($cluster['c1']);$j++)
				{
					$cb2['cb1'][$arCode] = ($cb2['cb1'][$arCode] + $cluster['c1'][$j][$arCode]);  
				}
				$cb2['cb1'][$arCode] = $cb2['cb1'][$arCode]/count($cluster['c1']);  

				$cb2['cb2'][$arCode] = 0;
	    		for($j=0;$j<count($cluster['c2']);$j++)
				{
					$cb2['cb2'][$arCode] = ($cb2['cb2'][$arCode] + $cluster['c2'][$j][$arCode]);  
				}  
				$cb2['cb2'][$arCode] = $cb2['cb2'][$arCode]/count($cluster['c2']);  
			}
			$kondisiCentroid = '';
			foreach ($mDataWilayah as $tmp) 
			{
				$arCode = 'ar_'.strtolower($tmp->kode_wilayah);
	    		for($j=1;$j<=2;$j++)
				{
					if($cb['cb'.$j][$arCode] == $cb2['cb'.$j][$arCode]){
						$kondisiCentroid = "c_sama";
					}else{
						$kondisiCentroid = "c_beda";
					}
				}
			}

	    	// echo $kondisiCentroid;
	    	if($kondisiCentroid == 'c_sama'){
	    		$prosesDelete = DB::table('t_bobot_wilayah_cluster')->delete();
	    		for ($i=0; $i < count($dataBobot); $i++) { 
	    			$prosesUpdate = DB::table('t_bobot_wilayah_cluster')
	    							->insert([
	    								'id_bobot' => $inputData[$i]['id'],
	    								'dc1' => $inputData[$i]['dc1'],
	    								'dc2' => $inputData[$i]['dc2'],
	    								'cluster' => $inputData[$i]['cluster']
	    							]);
	    		}
	    	}else{

	    	}
    	}
  //   	echo '<pre>';
		// // print_r($inputData);
		// print_r($cb);
		// print_r($cb2);
  //   	echo '</pre>';

  //   	foreach ($mDataWilayah as $tmp) 
		// {
  //   		for($j=0;$j<count($cluster['c1']);$j++)
		// 	{
		// 		$cb2['cb1'][$arCode] = ($cb2['cb1'][$arCode] + $cluster['c1'][$j][$arCode]);  
		// 	}
		// 	$cb2['cb1'][$arCode] = $cb2['cb1'][$arCode]/count($cluster['c1']);  

		// }
    }
}
