@extends('app.layouts.master')
@section('title')
    <title>کدهای کنترل</title>
    <link rel="stylesheet" href="{{ asset('assets/css/toggle-switch.css') }}">
@endsection

@section('content')
    <nav aria-label="breadcrumb" dir="rtl">
        <ol class="breadcrumb">
            <li class="breadcrumb-item font-size-12"> <a href="#">خانه</a></li>
            <li class="breadcrumb-item font-size-12"> <a href="#">تجهیزات</a></li>
            <li class="breadcrumb-item font-size-12 active" aria-current="page"> کدهای کنترل</li>
        </ol>
    </nav>


    <main>
        <section class="row">
            <section class="col-12">
                <section class="main-body-container">
                    <section class="main-body-container-header">
                        <h5>
                            کدهای کنترل
                        </h5>
                    </section>

                    <section class="d-flex justify-content-between align-items-center mt-4 mb-3 border-bottom pb-2">
                        <a href="{{ route('app.data-logger.order-code.create', $device->id) }}"
                            class="btn btn-info btn-sm text-light">
                            افزودن کد جدید</a>
                        <div class="max-width-16-rem">
                            <input type="text" class="form-control form-control-sm form-text" placeholder="جستجو">
                        </div>
                    </section>

                    <section class="table-responsive">
                        <table class="table table-striped font-size-14 table-bordered table-hover text-center">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>نام </th>
                                    <th>بازه زمانی (دقیقه) </th>
                                    <th> آخرین ارسال کد کنترلر</th>
                                    <th>وضعیت</th>

                                    <th class="max-width-16-rem text-center"><i class="fa fa-cogs"></i> تنظیمات</th>
                                </tr>
                            </thead>
                            <tbody>

                                @foreach ($device->order_codes as $orderCode)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $orderCode->name }}</td>
                                        <td>{{ $orderCode->readableTime }}</td>

                                        <td>{{ $orderCode->pivot->last_sent_at ? jalaliDate($orderCode->pivot->last_sent_at, 'Y/m/d H:i:s') : 'ارسال نشده' }}
                                        </td>

                                        <th>
                                            <label>
                                                <input id="orderCode-{{ $orderCode->pivot->order_code_id }}"
                                                    onchange="changeStatus(this)"
                                                    data-url="{{ route('app.data-logger.order-code.status', ['device' => $orderCode->pivot->datalogger_id, 'orderCode' => $orderCode->pivot->order_code_id]) }}"
                                                    type="checkbox" @if ($orderCode->pivot->status === 1) checked @endif>
                                            </label>
                                        </th>


                                        <td class="width-13-rem text-right font-size-2 ">

                                            <a href="{{ route('app.data-logger.order-code.edit',[ 'device' => $orderCode->pivot->datalogger_id , 'orderCode' => $orderCode->pivot->order_code_id] ) }}"
                                                class="btn btn-primary btn-sm"><i class="fa fa-edit"></i>
                                                ویرایش</a>
                                            {{-- <form action="{{ route('app.data-logger.destroy', $device->id) }}"
                                                method="POST" class=" btn btn-sm btn-danger">
                                                @csrf
                                                @method('delete')
                                                <button class="dropdown-item" type="submit"><i class="fa fa-trash-alt"></i>
                                                    حذف</button>
                                            </form> --}}



                                        </td>
                                    </tr>
                                @endforeach




                            </tbody>
                        </table>
                    </section>
                </section>
            </section>
        </section>
    </main>
@endsection
@section('script')
    <script type="text/javascript">
        function changeStatus(element) {
            var url = element.getAttribute('data-url');
        
            var isChecked = element.checked; // مقدار جدید چک‌باکس
            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            $.ajax({
                url: url,
                type: "POST",
                data: {
                    _token: csrfToken
                }, // ارسال CSRF Token
                success: function(response) {
                    if (response.status) {
                        if (response.checked) {
                            element.checked = true;
                            showToast('success', 'کد کنترلر فعال شد');
                        } else {
                            element.checked = false;
                            showToast('success', 'کد کنترلر غیرفعال شد');
                        }
                    } else {
                        element.checked = !isChecked;
                        showToast('error', 'هنگام ویرایش مشکلی بوجود آمد');
                    }
                },
                error: function() {
                    element.checked = !isChecked;
                    showToast('error', 'ارتباط برقرار نشد');
                }
            });
        }

        // تابع نمایش Toast برای پیام‌های موفقیت و خطا
        function showToast(type, message) {
            var bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
            var toastTag = `
        <section class="toast" data-delay="5000">
            <section class="toast-body py-3 d-flex ${bgClass} text-white">
                <strong class="ml-auto">${message}</strong>
                <button type="button" class="mr-2 close" data-dismiss="toast" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </section>
        </section>`;

            $('.toast-wrapper').append(toastTag);
            $('.toast').toast('show').delay(5500).queue(function() {
                $(this).remove();
            });
        }
    </script>


    @include('alerts.sweetalert.delete-confirm', ['className' => 'delete'])
@endsection
