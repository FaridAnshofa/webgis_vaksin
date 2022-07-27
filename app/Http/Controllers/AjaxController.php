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

class AjaxController extends Controller
{
    public function getDataWilayah()
    {
    	$id = $_GET['id'];
    	$data = DB::table('m_wilayah')
    			->where('id',$id)
    				->get();
    				
    	return response()->json($data);
    }

    public function dataCluster()
    {
    	$mDataWilayah	= DB::table('m_wilayah')->get();
    	// $dataCluster	= DB::table('t_bobot_wilayah as tbw')
					// 		->join('t_bobot_wilayah_cluster as tbwc','tbw.id','=','tbwc.id_bobot')
					// 			->get();
		$cluster = [];

		// for ($i=1; $i <= 2; $i++) { 
			$dataCluster	= DB::table('t_bobot_wilayah as tbw')
							->join('t_bobot_wilayah_cluster as tbwc','tbw.id','=','tbwc.id_bobot')
								->where('tbwc.cluster',2)
									->get();
			foreach ($dataCluster as $tmpCl) {
				foreach ($mDataWilayah as $tmp) {
	    			$arCode = 'ar_'.strtolower($tmp->kode_wilayah);
					$cluster['cluster2'][$arCode][$tmpCl->date] = $tmpCl->date.' : '.$tmpCl->$arCode;
					// $cluster['cluster'.$i][$arCode][$tmpCl->date]['val'] = $tmpCl->$arCode;
		    	}
			}
		// }

		echo '<pre>';
		print_r($cluster);
    	echo '</pre>';

		// return response()->json($dataCluster);
    }
}
