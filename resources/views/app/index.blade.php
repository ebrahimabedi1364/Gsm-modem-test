@extends('app.layouts.master')
@section('title')
    <link rel="stylesheet" href="{{ asset('assets/css/shape.css') }}">
@endsection

@section('content')
    <section class="container">
        @foreach ($dataloggers as $datalogger)
            @php
                $componentName = 'dataloggers.' . $datalogger->deviceSahpe; 
            @endphp
           

            {{-- <livewire:{{ 'dataloggers.'. $datalogger->deviceSahpe }} /> --}}

            

            <section class="shape">
                <section class="d-flex justify-content-between w-100">
                    <section class="status">

                       
                        @if (empty($datalogger->dataloggerLastStatus()))
                            {{ 'disconnect' }}
                        @else
                            {{ $datalogger->dataloggerLastStatus() }}
                            <span class="@if ($datalogger->dataloggerLastStatus() && $datalogger->power) {{ $datalogger->dataloggerLastStatus() }} @endif">

                            </span>
                        @endif

                    </section>

                    @livewire('datalogger-toggle', ['datalogger' => $datalogger], key($datalogger->id))

                   

                </section>
                @livewire($componentName , ['datalogger' => $datalogger], key($datalogger->id))

                

                <div class="d-flex pt-3 flex-column w-100">

                    <h5 class="d-flex ">{{ $datalogger->name ?? ' ' }}</h5>
                    <section class="d-flex justify-content-between">
                        <p class="text-secondary">{{  $datalogger->lastRecieveMessage()->time  }}</p>
                        <a href=""><i class="fas fa-reply text-secondary"></i></a>
                    </section>

                </div>


            </section>
        @endforeach
    </section>
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            $('.source').each(function() {
                var volume = $(this).data('volume'); // Get the volume from data attribute
                $(this).find('.source-level').css('height', volume +
                '%'); // Set the height of the water level
            });
        });
    </script>
@endsection
