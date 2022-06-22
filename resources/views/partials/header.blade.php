<div class="header mb-0">
    <div class="d-flex">
        <a class="navbar-brand" href="{{ url('/') }}">
            <img src="{{ asset('img/logo.jpg') }}" alt="logo" width="100px">
        </a>
        @if(isset($page_info['name']))
            <a href="#" class="btn my-auto" role="button" style="color: #777;">{{$page_info['name']}}</a>
        @endif
        @if (isset($page_info['project_name']))
            <div class="my-auto ml-2">&nbsp;|&nbsp;</div>
            <div class="ml-2 my-auto">{{$page_info['project_name']}}</div>
        @endif
        @if (session('company_name'))
            <div class="my-auto ml-2">&nbsp;|&nbsp;</div>
            <div class="ml-2 my-auto">{{session('company_name')}}</div>
        @endif
        @if (session('user_id'))
            <div class="my-auto ml-2">&nbsp;|&nbsp;</div>
            <div class="ml-2 my-auto">Job Share code - {{session('user_id')}}</div>
        @endif
    </div>

    <div class="d-flex my-auto">
        <a href="#" class="btn">
            <img src="{{asset('icons/ES_to_EN.png')}}" class="tkl-icon" alt="ES_to_EN">
        </a>

        <a href="#" class="btn">
            <img src="{{asset('icons/question.png')}}" class="tkl-icon" alt="Question">
        </a>

        <a href="#" class="btn">
            <img src="{{asset('icons/notification.png')}}" class="tkl-icon" alt="Notification">
        </a>

        <a href="https://takeofflite.com/logout" class="btn" title="Logout TKL">
            <img src="{{asset('icons/exit.png')}}" class="tkl-icon" alt="Logout TKL">
        </a>
    </div>

</div>