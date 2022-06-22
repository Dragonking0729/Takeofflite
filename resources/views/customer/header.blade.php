<div class="header mb-0">
    <div class="d-flex">
        <a class="navbar-brand" href="{{ url('/') }}">
            <img src="{{ asset('img/logo.jpg') }}" alt="logo" width="100px">
        </a>
        @if(isset($page_info['name']))
            <a href="#" class="btn my-auto" role="button" style="color: #777;">{{$page_info['name']}}</a>
        @endif
        @if (isset($page_info['company_info']['company_name']))
            <div class="ml-2 my-auto job-share-div"> - {{$page_info['company_info']['company_name']}}</div>
        @endif
    </div>
</div>