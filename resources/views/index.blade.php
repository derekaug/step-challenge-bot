@extends('layouts.base')

@section('content')
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <table id="leaderboard" class="table table-striped table-hover table-condensed">
                <colgroup>
                    <col style="width: 40px;"/>
                    <col/>
                    <col style="width: 150px;"/>
                </colgroup>
                <thead>
                <tr>
                    <th></th>
                    <th>Name</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>
                            <a href="{{ route('profile', ['user' => $user->name]) }}"></a>
                            <img src="{{ $user->image_avatar }}" class="img-responsive img-rounded"
                                 alt="{{ $user->full_name }}'s avatar"/>
                        </td>
                        <td>
                            <a href="{{ route('profile', ['user' => $user->name]) }}"></a>
                            {{ $user->full_name }}
                        </td>
                        <td class="text-right">
                            <a href="{{ route('profile', ['user' => $user->name]) }}"></a>
                            {{ $user->steps_display }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection