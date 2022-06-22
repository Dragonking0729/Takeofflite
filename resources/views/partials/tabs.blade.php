<div class="d-flex text-center py-1">
    <ul class="nav nav-tabs sheet-tab">
        <label class="first_group_label">DETAILED ESTIMATING</label>
        <li class="nav-item">
            <a href="{{url('documents/'.$page_info['project_id'].'/plan')}}"
               class="nav-link {{$page_info['name'] == 'Documents' || $page_info['name'] == 'Digitizer' ? 'active' : ''}}">
                DOCUMENTS
            </a>
        </li>
        <li class="nav-item">
            <a href="{{url('estimate/'.$page_info['project_id'])}}"
               class="nav-link px-2 {{$page_info['name'] == 'Estimate' ? 'active' : ''}}">
                COST ESTIMATES
            </a>
        </li>
    </ul>

    <ul class="nav nav-tabs sheet-tab">
        <!-- <label class="second_group_label">SALES, BILLING, AND PROJECT MANAGEMENT</label> -->
        <li class="nav-item">
            <a href="{{url('proposal/'.$page_info['project_id'])}}"
               class="nav-link px-2 {{$page_info['name'] == 'Proposals' ? 'active' : ''}}">
                PROPOSALS
            </a>
        </li>
        <!-- <li class="nav-item">
            <a href="{{url('invoice/'.$page_info['project_id'])}}"
               class="nav-link px-2 {{$page_info['name'] == 'Invoices' ? 'active' : ''}}">INVOICES</a>
        </li>
        {{--        <li class="nav-item">--}}
        {{--            <a href="#" class="nav-link px-2">--}}
        {{--                JOB COSTS--}}
        {{--            </a>--}}
        {{--        </li>--}}
        <li class="nav-item">
            <a href="{{url('daily_logs/'.$page_info['project_id'])}}"
               class="nav-link px-2 {{$page_info['name'] == 'Daily Logs' ? 'active' : ''}}">DAILY LOGS</a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link px-2">
                COMMUNICATIONS
            </a>
        </li> -->

    </ul>
    <!-- <ul class="nav nav-tabs sheet-tab">
        <label class="third_group_label">REAL ESTATE INVESTING</label>
        <li class="nav-item">
            <a href="{{url('flip/'.$page_info['project_id'])}}"
               class="nav-link px-2 {{$page_info['name'] == 'Flip Analyzer' ? 'active' : ''}}">FLIP ANALYZER</a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link px-2">
                BRRRR ANALYZER
            </a>
        </li>
    </ul> -->

    <ul class="nav nav-tabs non-group">
        <li class="nav-item">
            <a href="#" class="nav-link px-2">
                REPORTS AND EXPORTS
            </a>
        </li>
    </ul>
</div>