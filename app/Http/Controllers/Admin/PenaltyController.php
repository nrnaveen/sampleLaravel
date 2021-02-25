<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class PenaltyController extends Controller {

	public function index(Request $request){
		try{
			$queryParam = 'id';
			$sort = 'DESC';
			$search = null;
			$q = null;
			if($request->has('query')){ $queryParam = $request->get('query'); }
			if($request->has('sort')){ $sort = $request->get('sort'); }
			if($request->has('q')){
				$search = $request->get('q');
				$q = '%' . $request->get('q') . '%';
			}
			$query = new \App\Penalty();
			if($search){ $query = $query->whereRaw("beginning LIKE ? OR ending LIKE ? OR total_duration LIKE ? OR type LIKE ?", [$q, $q, $q, $q]); }
			$penalties = $query->orderBy($queryParam, $sort)->paginate(15);
			$penalties->appends(request()->except(['page', '_token']));
			return View('admin.penalty.index', [
				'penalties' => $penalties,
				'title' => trans('messages.PenaltyManagement'),
				'queryParam' => $queryParam,
				'sort' => $sort,
				'search' => $search,
			]);
		}catch(\Exception $e){
			return redirect('/admin')->withError($e->getMessage());
		}
	}

	public function getEdit($id){
		try{
			$penalty = \App\Penalty::find($id);
			if($penalty) return View('admin.penalty.edit', ['penalty' => $penalty, 'title' => trans('messages.PenaltyEdit')]);
			else return redirect('/admin/penalties')->withError(trans('messages.PenaltyNotFound'));
		}catch(\Exception $e){
			return redirect('/admin/penalties')->withError($e->getMessage());
		}
	}

	public function postEdit(Request $request, $id){
		try{
			$penalty = \App\Penalty::find($id);
			if($penalty){
				$data = $request->only(['beginning', 'ending', 'type']);
				$all = round((strtotime($data['ending']) - strtotime($data['beginning'])) / 60);
				$d = floor ($all / 1440);
				$h = floor (($all - $d * 1440) / 60);
				$m = $all - ($d * 1440) - ($h * 60);
				if($d > 0){
					$h = $h + (24 * $d);
				}
				$total = ($h > 10 ? $h : '0' . $h) . ':' . ($m > 0 ? $m : '00');
				$data['total_duration'] = $total;
				$insert = $penalty->update($data);
				if($insert) return redirect('/admin/penalties')->withMessage(trans('messages.PenaltyUpdatedSuccessfully'));
				else return redirect()->back()->withError(trans('messages.SomethingwentWrong'))->withInput();
			}else return redirect('/admin/penalties')->withError(trans('messages.PenaltyNotFound'));
		}catch(\Exception $e){
			return redirect('/admin/penalties')->withError($e->getMessage());
		}
	}

	public function deletePenalty(Request $request, $id){
		try{
			$penalty = \App\Penalty::find($id);
			if($penalty){
				$penalty->delete();
				return redirect('/admin/penalties')->withMessage(trans("messages.PenaltyDeletedSuccessfully"));
			}else return redirect('/admin/penalties')->withError(trans('messages.PenaltyNotFound'));
		}catch(\Exception $e){
			return redirect('/admin/penalties')->withError($e->getMessage());
		}		
	}

	public function excelExport(Request $request){
		try{
			$excel = \App::make('excel');
			$penalties = \App\Penalty::orderBy('id', 'DESC')->get();
			$datasheet = array();
			$datasheet[0] = array('id', 'firstname', 'lastname', 'mission', 'beginning', 'ending', 'total_duration', 'type', 'at_home', 'comments', 'client_informed',);
			foreach($penalties as $key => $datanew){
				$datasheet[$key + 1] = array($datanew->id,
					$datanew->user->firstname,
					$datanew->user->lastname,
					$datanew->mission->code,
					$datanew->beginning,
					$datanew->ending,
					$datanew->total_duration,
					$datanew->type,
					$datanew->at_home,
					$datanew->comments,
					$datanew->client_informed,
				);
			}
			return Excel::create('penalties', function($excel) use($datasheet){
				return $excel->sheet('Sheet 1', function($sheet) use($datasheet){
					$sheet->fromArray($datasheet);
				});
			})->download('xlsx');
		}catch(\Exception $e){
			return redirect('/admin/users')->withError($e->getMessage());
		}
	}
}