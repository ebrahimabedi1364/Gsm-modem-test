@extends('app.layouts.master')

@section('title')
    <title> کدهای کنترل </title>
@endsection

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item font-size-12"> <a href="#">خانه</a></li>
            <li class="breadcrumb-item font-size-12"> <a href="#">تجهیزات</a></li>
            <li class="breadcrumb-item font-size-12 active" aria-current="page">کدهای کنترل</li>
        </ol>
    </nav>

    <section class="row">

        <section class="col-12">

            <section class="main-body-container">

                <section class="main-body-container-header">

                    <h5> ویرایش کدهای کنترل</h5>
                    <p></p>

                </section>

                <section class="d-flex justify-content-between align-items-center mt-4 mb-3 border-bottom pb-2">

                    <a class="btn btn-info btn-sm text-light" href="{{ route('app.data-logger.index') }}">بازگشت </a>

                </section>

                <section>

                    <form action="{{ route('app.data-logger.order-code.update', ['device'=>$device->id , 'orderCode' => $orderCode->id] ) }}" method="POST">
                        @csrf
                        @method('put')

                        <section class="row   mb-3">

                            <section class="col-6 col-md-3">
                                <div class="form-group">
                                    <label for="">کد کنترل </label>



                                    <select class=" form-control form-control-sm" name="order_code_id">

                                        @foreach ($orderCodes as $orderCode)
                                            <option value="{{ $orderCode->id }}"
                                                @foreach ($device->order_codes as $deviceOrderCode)
        
                                                @if ($deviceOrderCode->id === $orderCode->id)
                                                selected
                                                @endif @endforeach>
                                                {{ $orderCode->name }}</option>
                                        @endforeach

                                    </select>

                                </div>
                                @error('order_code_id')
                                    <span class="alert_required bg-danger text-white p-1 rounded" role="alert">
                                        <strong>
                                            {{ $message }}
                                        </strong>
                                    </span>
                                @enderror

                            </section>

                            <section class="col-12 col-md-6 ">
                                <div class="form-group">
                                    <label for="time">بازه زمانی ارسال </label>
                                    <select name="time" id="time" class="form-control form-control-sm">
                                        <option value="">بازه زمانی را انتخاب کنید</option>
                                        <option value="60" @if (old('time', $orderCode->time) == '60') selected @endif>هر 1 ساعت
                                        </option>
                                        <option value="30" @if (old('time', $orderCode->time) == '30') selected @endif>هر 30دقیقه
                                        </option>
                                        <option value="15" @if (old('time', $orderCode->time) == '15') selected @endif>هر 15 دقیقه
                                        </option>
                                        <option value="10" @if (old('time', $orderCode->time) == '10') selected @endif>هر 10 دقیقه
                                        </option>

                                    </select>
                                    @error('time')
                                        <span class="alert_required bg-danger text-white p-1 rounded" role="alert">
                                            <strong>
                                                {{ $message }}
                                            </strong>
                                        </span>
                                    @enderror
                                </div>

                            </section>


                        </section>

                        <section class="col-12 col-md-6">
                            <button class="btn btn-primary btn-sm">ثبت</button>
                        </section>


                    </form>


                </section>

            </section>

        </section>

    </section>
@endsection
@section('script')
    {{-- <script>
        $(function() {

            $('#btn-copy').on('click', function() {

                var ele = $(this).parent().prev().clone(true);
                $(this).before(ele);

            });



        });
    </script>

    <script>
         $(document).on('click', '.remove-btn', function() {
               

                if ($(this).closest('.dynamic-fields').index() >= 0) {
                    $(this).closest('.dynamic-fields').remove();
                } 
            });
    </script> --}}
@endsection
