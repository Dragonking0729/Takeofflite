@extends('layouts.app')

@section('title')
    {{ trans('titles.daily_logs') }}
@endsection

@section('custom_css')

    <link rel="stylesheet" href="{{ asset('css/dist/themes/default/style.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/daily_logs.css') }}"/>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.css"/>

@endsection

@section('content')
    @include('partials.tabs')

    <div class="container-fluid">
        {{-- daily logs toolbar --}}
        <div class="row daily_logs_toolbar">
            <div class="col-md-12 d-flex pl-0 align-items-center">
                <button type="button" class="btn btn-sm btn-link mr-2 select_all" data-checked="0"
                        title="Select All">
                </button>
                <button type="button" class="btn btn-sm btn-link mr-2 create_new_daily_logs"
                        data-toggle="modal" data-target="#new_daily_log_modal" title="New Daily Log">
                </button>
                <button type="button"
                        class="btn btn-sm btn-link mr-2 remove_daily_logs disabled"
                        title="Delete daily logs"
                        data-id="">
                </button>
                <button type="button" class="btn btn-sm btn-link mr-2 print_daily_logs"
                        title="Print">
                </button>
            </div>
        </div>
        {{-- end daily logs toolbar --}}

        {{-- daily logs --}}
        <div style="margin-bottom: 75px;">
            @foreach($daily_logs as $index => $log)
                <div class="daily_log_item">
                    <div class="daily_log_item_left">
                        <button type="button" class="btn select_daily_log_item"
                                data-id="{{$log->id}}" data-checked="0"></button>
                        <span class="daily_log_detail_open_close" data-open="0"></span>
                    </div>
                    <div class="daily_log_item_right">
                        <div class="row py-2 daily_log_item_right_top">
                            <div class="col-md-3">
                                <label for="log_entry_date__{{$log->id}}">Log entry date/time</label>
                                <input type="datetime-local" class="form-control log_entry_date_picker"
                                       id="log_entry_date__{{$log->id}}"
                                       value="{{$log->log_entry_date}}" data-field="log_entry_date"
                                       data-id="{{$log->id}}">
                            </div>
                            <div class="col-md-3">
                                <img src="{{asset('storage/'.$log->weather)}}"
                                     width="170" height="100" alt="Weather"
                                     title="Forecast from weatherUSA"/>
                            </div>
                            <div class="col-md-3 text-center my-auto">
                                <label for="customer_can_view__{{$log->id}}">
                                    <span class="customer_can_view_label">Customer can view?</span>
                                </label>
                                <button type="button" id="customer_can_view__{{$log->id}}"
                                        class="btn btn-sm btn-link mr-2 customer_can_view {{$log->customer_view ? 'checked' : 'unchecked'}}"
                                        data-checked="{{$log->customer_view}}" data-id="{{$log->id}}">
                                </button>
                            </div>
                            <div class="col-md-3 d-flex justify-content-center my-auto">
                                <form action="javascript:void(0)" method="POST"
                                      enctype="multipart/form-data" id="attach_more_files_form" data-id="{{$log->id}}">
                                    @csrf
                                    <button type="button"
                                            class="btn btn-sm btn-link daily_log_attach" title="Attach log file">
                                    </button>
                                    <input type="file" style="display: none;" id="attach_more_files"
                                           name="attach_more_files[]" multiple>
                                    <input type="hidden" name="log_id" value="{{$log->id}}">
                                </form>
                            </div>
                        </div>

                        <div class="row py-2 daily_log_item_right_bottom">
                            <div class="col-md-12">
                                <textarea class="form-control note" rows="2" data-id="{{$log->id}}"
                                          data-field="note">{{$log->note}}</textarea>
                            </div>

                            {{-- attached files --}}
                            <div class="row" id="attached_files_list_{{$log->id}}">
                                @foreach($log->files as $file)
                                    <div class="col-md-1 pb-2 pt-2">
                                        <div>
                                            @if($file->type === 'svg' || $file->type === 'jpg' || $file->type === 'jpeg'
                                            || $file->type === 'png')
                                                <a href="{{asset($file->path)}}" data-fancybox="attached_files_list_{{$log->id}}"
                                                   data-caption="{{$file->name}}">
                                                    <img src="{{asset($file->path)}}" alt="{{$file->name}}"
                                                         class="img-thumbnail rounded"
                                                         style="width:100px;" title="{{$file->name}}">
                                                </a>
                                            @else
                                                <a href="{{asset('/icons/noun_other_3482826.png')}}"
                                                   data-fancybox="attached_files_list_{{$log->id}}" data-caption="{{$file->name}}">
                                                    <img src="{{asset('/icons/noun_other_3482826.png')}}"
                                                         class="img-thumbnail rounded"
                                                         alt="{{$file->name}}" style="width:100px;"
                                                         title="{{$file->name}}">
                                                </a>
                                            @endif
                                        </div>
                                        <div class="d-flex">
                                            <div class="attach_file_name">{{$file->name}}</div>
                                            <div>
                                                <a href="{{asset($file->path)}}" download>
                                                    <i class="fa fa-download"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            {{-- end attached files --}}

                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        {{-- end daily logs --}}
    </div>


    @include('modals.daily_logs.new_daily_log')

@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@4.0/dist/fancybox.umd.js"></script>
    @include('scripts.daily_logs.daily-logs-js')
@endsection