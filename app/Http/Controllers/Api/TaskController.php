<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\UserTask;
use App\Models\Progress;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\UserAssignedTask;
use Validator;
use DB;
use Carbon\Carbon;

class TaskController extends BaseController
{

    // const WEEKLY = 10;
    // const MONTHLY = 20;
    // const YEARLY   = 30;

    // 1 = professional
    // 2= personal

    public function index(Request $request)
    {
        $tasks = UserAssignedTask::query();

        if ($request->user_id) {
            $userId = $request->user_id;
            $tasks->where('user_id', $userId);
        }

        if ($request->type && $request->type == "10") {
            $tasks->whereBetween(
                'created_at',
                [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]
            );
        }

        if ($request->type && $request->type == "20") {
            $tasks->whereBetween(
                'created_at',
                [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]
            );
        }

        if ($request->type && $request->type == "30") {
            $tasks->whereBetween(
                'created_at',
                [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()]
            );
        }

        if ($request->category_id) {
            $category_id = $request->category_id;
            $tasks->whereHas('task', function ($query) use ($category_id) {
                $query->where('category_id', '=', $category_id);
            });
        }

        if ($request->created_at) {
            $date = Carbon::parse(Carbon::createFromFormat('d/m/Y', $request->created_at));
            $startOfWeek = Carbon::parse($date)->startOfWeek();
            $endOfWeek = Carbon::parse($date)->endOfWeek();
            // dd()
            $tasks->whereBetween(
                'created_at',
                [$startOfWeek, $endOfWeek]
            );
        }else{
            $tasks->whereBetween(
                'created_at',
                [Carbon::today()->startOfWeek(), Carbon::today()->endOfWeek()]
            );
        }

        $result = $tasks->paginate();
        if ($result) {
            for ($i = 0; $i < count($result); $i++) {
                # code...
                $todayProgress = 0;
                if($request->created_at){
                    $startOfDay = Carbon::parse($request->created_at)->startOfDay()->format('d/m/Y');
                    $endOfDay = Carbon::parse($request->created_at)->endOfDay()->format('d/m/Y');
                    $todayProgress = DB::table('progress')
                    ->where('progress.task_id', '=', $result[$i]['id'])
                    ->where('progress.user_id', '=', $request->user_id)
                    ->whereBetween('progress.progress_date', [$request->created_at, $request->created_at])
                    ->sum('progress.progress_value');
                }else{
                    $startOfDay = Carbon::now()->startOfDay()->format('d/m/Y');
                    $endOfDay = Carbon::now()->endOfDay()->format('d/m/Y');
                    $todayProgress = DB::table('progress')
                    ->where('progress.task_id', '=', $result[$i]['id'])
                    ->where('progress.user_id', '=', $request->user_id)
                    ->whereDate('progress.progress_date','=',$endOfDay)
                    ->whereDate('progress.progress_date','>=',$startOfDay)
                    ->sum('progress.progress_value');
                }

                $prevProgressCount = DB::table('progress')
                    ->where('progress.task_id', '=', $result[$i]['id'])
                    ->where('progress.user_id', '=', $request->user_id)
                    ->sum('progress.progress_value');

                $result[$i]['totalProgress'] = $prevProgressCount;
                $result[$i]['totalPercent'] = round(($prevProgressCount / $result[$i]['task']['goal']) * 100); //add relation task to check goal
                $result[$i]['todayProgress'] = $todayProgress;
            }
        }
        $mappedResult = [];
        $data = $this->mapperTaskListing($result);
        $mappedResult['data'] = $data;
        $mappedResult['per_page'] = $result->perPage();
        $mappedResult['current_page'] = $result->currentPage();
        $mappedResult['total'] = $result->total();
        $mappedResult['last_page'] = $result->lastPage();

        return $this->sendResponse($mappedResult, 'All Tasks Listing');
    }

    function get_local_time(){
  
        $ip = file_get_contents("http://ipecho.net/plain");
        $url = 'http://ip-api.com/json/'.$ip;
        $tz = file_get_contents($url);
        $tz = json_decode($tz,true)['timezone'];
    
        return $tz;
    }


    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'goal' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        try {

            if ($request->file('image')) {
                $image = $request->file('image');
                //store Image to directory
                $imgName = rand() . '_' . time() . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('task_icons');
                $imagePath = $destinationPath . "/" . $imgName;
                $image->move($destinationPath, $imgName);
                $path = "task_icons" . "/" . basename($imagePath);
            }

            DB::beginTransaction();

            $task = new UserTask;
            $task->title = $request->title;
            $task->description = $request->description;
            $task->goal = $request->goal;
            $task->type = $request->type ? $request->type : "WEEKLY";
            $task->user_id = auth()->user()->id;
            $task->image = $request->file('image') ? $path : '';
            $task->category_id = $request->category_id ? $request->category_id : '2';
            $task->save();

            $assignedTask = new UserAssignedTask;
            $assignedTask->task_id = $task->id;
            $assignedTask->user_id = auth()->user()->id;

            $assignedTask->save();

            DB::commit();

            $task->id = $assignedTask->id;

            return $this->sendResponse($assignedTask, "Task Created Successfully!");
        } catch (\Throwable $th) {

            DB::rollBack();
            return $this->sendError('Something went wrong', $th->getMessage());
        }
    }

    public function show($id)
    {


        $task = UserAssignedTask::with('task', 'task.progress')->find($id);

        if (!$task) {
            return $this->sendError('Task By this ID doest not exist', null);
        }

        $preRecord = [];

        $record = [];

        $prevProgressCount = DB::table('progress')
            ->where('progress.task_id', '=', $task->id)
            ->where('progress.user_id', '=', auth()->user()->id)
            ->sum('progress.progress_value');

        $task->totalProgress = $prevProgressCount;
        $task->totalPercent = round(($prevProgressCount / $task->task->goal) * 100);

        $allProgress = Progress::where('task_id', $task->id)->get();

        if ($allProgress) {
            foreach ($allProgress as $key => $value) {
                // $record['date'] = Carbon::parse($value['progress_date'])->format("m-d-Y");
                $record['date'] = $value['progress_date'];
                $record['value'] = $value['progress_value'];
                $record['user_id'] = $value['user_id'];
                array_push($preRecord, $record);
            }
        }

        // Create an empty array to store the values for each date
            $result = [];

            // Loop through the data array
            foreach ($preRecord as $item) {
                $date = $item['date'];
                $value = $item['value'];
                // If the date already exists in the result array, add the new value to the existing one
                if (array_key_exists($date, $result)) {
                    $result[$date]['value'] += $value;
                }
                // If the date doesn't exist, add it to the result array as a new item
                else {
                    $result[$date] = $item;
                }
            }

            // Update the original array with the result array
            $dataTotalPogress = array_values($result);

        $data = $this->mapperTask($task);

        $data->allProgress = $dataTotalPogress;

        return $this->sendResponse($data, 'Task Listing');
    }

    function mapperTaskListing($items)
    {
        return $items->map(function ($item, $key) {
            return  [
                "id" => $item->id,
                "title" => $item->task->title,
                "description" =>  $item->task->description,
                "goal" => $item->task->goal,
                "type" => $item->task->type,
                "category_id" => $item->task->category_id,
                "created_at" => $item->created_at,
                "updated_at" => $item->updated_at,
                "image" => $item->task->image,
                "totalProgress" => $item->totalProgress,
                "totalPercent" => $item->totalPercent,
                "todayProgress" => $item->todayProgress,
            ];
        });
    }

    function mapperTask($item)
    {
        return (object) [
            "id" => $item->id,
            "title" => $item->task->title,
            "description" =>  $item->task->description,
            "goal" => $item->task->goal,
            "type" => $item->task->type,
            "created_at" => $item->created_at,
            "updated_at" => $item->updated_at,
            "image" => $item->task->image,
            "totalProgress" => $item->totalProgress,
            "totalPercent" => $item->totalPercent,
        ];
    }

    public function delete($id)
    {

        $task = UserAssignedTask::find($id);

        if (!$task) {
            return $this->sendError('Task By this ID doest not exist', null);
        }

        // $parentTask = UserTask::where('id',$task->task_id)->first();

        // if($parentTask){
        //     $parentTask->delete();
        // }

        Progress::where('task_id',$id)->delete();

        $task->delete();

        return $this->sendResponse(null, 'Task Deleted Successfully!');
    }

    public function update(Request $request, $id)
    {

        $task = UserTask::find($id);

        if (!$task) {
            return $this->sendError('Task By this ID doest not exist', null);
        }

        $task->title = $request->title ? $request->title : $task->title;
        $task->description = $request->description ? $request->description : $task->description;
        $task->goal = $request->goal ? $request->goal : $task->goal;
        $task->type = $request->type ? $request->type : $task->type;
        $task->category_id = $request->category_id ? $request->category_id : $task->category_id;

        $task->save();

        return $this->sendResponse($task, 'Task Updated Successfully!');
    }

    public function storeProgress(Request $request, $id)
    {

        $task = UserAssignedTask::find($id);

        if (!$task) {
            return $this->sendError('Task By this ID doest not exist', null);
        }

        $validator = Validator::make($request->all(), [
            'progress_value' => 'required',
            'progress_date' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        if ($request->progress_value > $task->task->goal) {
            return $this->sendError('Progress Value can not be greater than Total Goal', null);
        }

        $prevProgressCount = DB::table('progress')
            ->where('progress.task_id', '=', $id)
            ->sum('progress.progress_value');

        if ($prevProgressCount == $task->task->goal) {
            return $this->sendError('You have already completed Task', null);
        }

        try {

            DB::beginTransaction();

            $progress = new Progress;
            $progress->progress_value = $request->progress_value;
            $progress->progress_date = $request->progress_date;
            $progress->task_id = $id;
            $progress->user_id = auth()->user()->id;
            $progress->save();

            DB::commit();

            return $this->sendResponse($progress, "Progress Created Successfully!");
        } catch (\Throwable $th) {

            DB::rollBack();

            return $this->sendError('Something went wrong', $th->getMessage());
        }
    }

    public function analytics(Request $request)
    {

        $data = 0;
        $totalProgressCount = 0;


        if (!$request->user_id) {
            $this->sendError('User ID is required, Please send user_id in query param.', null);
        }
        $tasks  = UserAssignedTask::latest();
        if ($request->type && $request->type == "10") {
            $tasks->whereBetween(
                'created_at',
                [Carbon::today()->startOfWeek(), Carbon::today()->endOfWeek()]
            );
        }

        if ($request->type && $request->type == "20") {
            $tasks->whereBetween(
                'created_at',
                [Carbon::today()->startOfMonth(), Carbon::today()->endOfMonth()]
            );
        }

        if ($request->type && $request->type == "30") {
            $tasks->whereBetween(
                'created_at',
                [Carbon::today()->startOfYear(), Carbon::today()->endOfYear()]
            );
        }

        if ($request->user_id) {
            $tasks->where('user_id', $request->user_id);
        }

        if ($request->category_id) {
            $category_id = $request->category_id;
            $tasks->whereHas('task', function ($query) use ($category_id) {
                $query->where('category_id', '=', $category_id);
            });
        }

        $totalSumOfGoals = 0;
        $totalTaskCount = 0;

        $allTask = $tasks->get();

        if ($allTask) {
            foreach ($allTask as $key => $value) {

                if ($request->type && $request->type == 10) {
                    $totalTaskCount = $value['task']['goal'];
                    $totalSumOfGoals += $totalTaskCount;
                    $totalProgressCount += Progress::where('user_id', $request->user_id)
                        ->where('task_id', $value['id'])
                        ->sum('progress_value');
                }
                if ($request->type && $request->type == 20) {
                    $totalTaskCount = $value['task']['goal'] * 4;
                    $totalSumOfGoals += $totalTaskCount;
                    $totalProgressCount += Progress::where('user_id', $request->user_id)
                        ->where('task_id', $value['id'])
                        ->sum('progress_value');
                }
                if ($request->type && $request->type == 30) {
                    $totalTaskCount = $value['task']['goal'] * 52;
                    $totalSumOfGoals += $totalTaskCount;
                    $totalProgressCount += Progress::where('user_id', $request->user_id)
                        ->where('task_id', $value['id'])->sum('progress_value');
                }
            }
        }

        if ($totalSumOfGoals == 0) {
            return $this->sendResponse($data, "User Analytics",);
        }
        $data = round(($totalProgressCount / $totalSumOfGoals) * 100);
        return $this->sendResponse($data, "User Analytics",);
    }

    public function deleteAllTask()
    {
        # code...

        UserTask::truncate();
        UserAssignedTask::truncate();
        Progress::truncate();

        return response()->json("Deleted Successfully!");
    }

    public function assignAllTasksOnWeekEnd(){
        $result = UserAssignedTask::select('task_id','user_id')->groupBy('task_id','user_id')->get();
        if($result){
            DB::beginTransaction();
            foreach($result as $r){
                $assignedTask = new UserAssignedTask;
                $assignedTask->user_id = $r->user_id;
                $assignedTask->task_id = $r->task_id;
                $assignedTask->save();
            }
            DB::commit();
        }
        
        return $this->sendResponse(200, "All Tasks");
    }    
    
}
