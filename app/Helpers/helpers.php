<?php

use App\Models\Progress;
function getTotalProgressOfTask($id,$userId){

    $prevProgressCount = Progress::
         where('id', '=', $id)
        ->sum('progress_value');
      
    return $prevProgressCount;
}

function getPercentTotalOfTask($totalProgress,$goal){

    $totalPercent = round(($totalProgress / $goal) * 100); //add relation task to check goal

    return $totalPercent;
}


?>