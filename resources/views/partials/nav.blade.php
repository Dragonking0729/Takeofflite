<nav class="navbar navbar-expand-sm navbar-dark navbar-custom-bg justify-content-start">
    <div class="d-flex" style="margin-left: 200px;">
        <a href="{{ url('/dashboard') }}" class="mt-auto mb-auto text-white" title="Return to Dashboard">
            <img src="{{asset('icons/dashboard.svg')}}" width="25px">
        </a>
        <div class="dropdown">
            <a class="btn bg-gradient-primary text-white dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                @if(!empty($page_info) && isset($page_info['project_name']))
                    {{$page_info['project_name']}}
                @else
                    Select Project
                @endif
            </a>

            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                @if (isset($projects) && count($projects))
                    @foreach ($projects as $project)
                        @if(!$project['isShared'])
                            <a class="dropdown-item"
                               href="{{ url('/estimate'.'/'.$project['id']) }}">{{ $project['projectName'] }}</a>
                        @endif
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</nav>