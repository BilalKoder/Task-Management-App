<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Tasks
    </h2>
</x-slot>
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg px-4 py-4">
            @if (session()->has('message'))
                <div class="bg-teal-100 border-t-4 border-teal-500 rounded-b text-teal-900 px-4 py-3 shadow-md my-3"
                    role="alert">
                    <div class="flex">
                        <div>
                            <p class="text-sm">{{ session('message') }}</p>
                        </div>
                    </div>
                </div>
            @endif
            @if (Request::getPathInfo() == '/dashboard/tasks')
                <button wire:click="create()"
                    class="inline-flex items-center px-4 py-2 my-3 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:shadow-outline-gray disabled:opacity-25 transition ease-in-out duration-150">
                    Create New Task
                </button>
            @endif

           
            
            <table class="table-fixed w-full">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 w-20">No.</th>
                        <th class="px-4 py-2">Name</th>
                        <th class="px-4 py-2">Category</th> 
                        <th class="px-4 py-2">Weekly Goal</th>
                        <th class="px-4 py-2">Total Progress</th>
                        <th class="px-4 py-2">Total Progress(%)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $count = 1; ?>
                    @foreach($tasks as $task)
                    <tr>
                        <td class="border px-4 py-2"><?php echo $count++; ?></td>
                        <td class="border px-4 py-2">{{ $task->task->title }} </td>
                         <td class="border px-4 py-2">{{ $task->task->category->title }}</td> 
                        <td class="border px-4 py-2">{{ $task->task->goal }}</td>
                        @php
                            $totalProgressCount = getTotalProgressOfTask($task->id,$user_id);
                        @endphp
                        <td class="border px-4 py-2">{{$totalProgressCount}}</td>

                        <td class="border px-4 py-2">{{
                        ($totalProgressCount > 0) ?
                            getPercentTotalOfTask($totalProgressCount,$task->task->goal) : 0 }}                        
                            % </td>

                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="py-4">
            {{ $tasks->links() }}
        </div>
    </div>
</div>
