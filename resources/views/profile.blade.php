@extends('layouts.base')

@section('content')
    <div class="row">
        <div class="col-md-2 col-md-offset-2">
            <img class="avatar img-responsive img-rounded" src="{{ $user->image_avatar }}"
                 alt="{{ $user->full_name }}'s avatar"/>
            <h4 class="text-center">{{ $user->full_name }}</h4>
            <h5 class="text-center">{{ $user->steps_display }}</h5>
            <div class="separator-line hidden-md hidden-lg"></div>
        </div>
        <div class="col-md-6">
            <h4>Logged Steps</h4>
            <table id="step-log" class="table table-striped table-hover table-condensed">
                <colgroup>
                    <col/>
                    <col style="width: 150px;"/>
                </colgroup>
                <thead>
                <tr>
                    <th>Date(s)</th>
                    <th></th>
                </tr>
                </thead>
                <tfoot>
                    <tr>
                        <td class="text-right"><strong>Total: </strong></td>
                        <td class="text-right"><strong>{{ $user->steps_display }}</strong></td>
                    </tr>
                </tfoot>
                <tbody>
                @foreach ($logs as $log)
                    <tr>
                        <td>{{ $log->date_display }}</td>
                        <td class="text-right">{{ $log->steps_display }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection