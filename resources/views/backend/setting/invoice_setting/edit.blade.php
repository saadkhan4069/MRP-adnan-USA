@extends('backend.layout.main')
@push('css')
@endpush

@section('content')


    <x-error-message key="not_permitted" />

    <section class="forms">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h4>{{ __('db.Edit Invoice Setting') }}</h4>
                        </div>
                        <div class="card-body">
                            <p class="italic">
                                <small>{{ __('db.The field labels marked with * are required input fields') }}.</small>
                            </p>

                            <form action="{{ route('settings.invoice.update', $invoice->id) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <div class="col-md-4 form-group">
                                        <label><strong>{{ __('db.Invoice Type') }}</strong></label>
                                        <select name="size" class="form-control">
                                            <option value="a4" {{ $invoice->size == 'a4' ? 'selected' : '' }}>A4
                                            </option>
                                            <option value="58mm" {{ $invoice->size == '58mm' ? 'selected' : '' }}>58mm
                                                (Thermal
                                                receipt)</option>
                                            <option value="80mm" {{ $invoice->size == '80mm' ? 'selected' : '' }}>80mm
                                                (Thermal
                                                receipt)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label><strong>{{ __('db.Template Name') }} *</strong></label>
                                        <input type="text" name="template_name"
                                            value="{{ old('template_name', $invoice->template_name) }}" required
                                            class="form-control">
                                        @error('template_name')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="col-md-4 form-group">
                                        <label><strong>{{ __('db.Prefix') }} *</strong></label>

                                        <input type="text" id="prefix" name="prefix" class="form-control" required
                                            minlength="2" maxlength="11" value="{{ old('prefix', $invoice->prefix) }}">
                                        <small id="prefix-message" style="display: block; margin-top: 5px;"></small>

                                        @error('prefix')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="col-md-4 form-group">
                                        <label><strong>{{ __('db.Numbering Type') }} *</strong></label>
                                        <select name="numbering_type" class="form-control numberingType" required>
                                            <option value="sequential"
                                                {{ $invoice->numbering_type === 'sequential' ? 'selected' : '' }}>
                                                {{ __('db.Sequential') }}</option>
                                            <option value="random"
                                                {{ $invoice->numbering_type === 'random' ? 'selected' : '' }}>
                                                {{ __('db.Random') }}</option>
                                            <option value="datewise"
                                                {{ $invoice->numbering_type === 'datewise' ? 'selected' : '' }}>
                                                {{ __('db.Date Wise') }}</option>

                                        </select>
                                    </div>

                                    <div class="col-md-4 form-group digit_number">
                                        <label><strong>{{ __('db.Number Of Digit') }} *</strong></label>
                                        <input type="number" name="number_of_digit"
                                            value="{{ old('number_of_digit', $invoice->number_of_digit) }}" required
                                            class="form-control">
                                        @error('number_of_digit')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>


                                    <div class="col-md-4 form-group start_number">
                                        <label><strong>{{ __('db.Start Number') }}</strong></label>
                                        <input type="text" name="start_number"
                                            value="{{ old('start_number', $invoice->start_number) }}" class="form-control">
                                    </div>


                                    <div class="col-md-4 form-group">
                                        <label><strong>{{ __('db.Header Text') }}</strong></label>
                                        <input type="text" class="form-control validate-input" name="header_text"
                                            id="header_text" data-min="2" data-max="100"
                                            data-target="#header-text-message"
                                            value="{{ old('header_text', $invoice->header_text) }}">
                                        <small id="header-text-message" style="display: block; margin-top: 5px;"></small>
                                    </div>

                                    <div class="col-md-4 form-group">
                                        <label><strong>{{ __('db.Footer Text') }}</strong></label>
                                        <input type="text" class="form-control validate-input" name="footer_text"
                                            id="footer_text" data-min="2" data-max="100"
                                            data-target="#footer-text-message"
                                            value="{{ old('footer_text', $invoice->footer_text) }}">
                                        <small id="footer-text-message" style="display: block; margin-top: 5px;"></small>
                                    </div>

                                    <div class="col-md-4 form-group">
                                        <label><strong>{{ __('db.Company Logo') }}</strong></label>
                                        <input type="file" name="company_logo" class="form-control">
                                        @if ($invoice->company_logo)
                                            <img src="{{ $invoice->company_logo }}" height="50" class="mt-2">
                                        @endif
                                    </div>

                                    <div class="col-md-4 form-group">
                                        <label><strong>{{ __('db.Logo Height') }}</strong></label>
                                        <input type="number" name="logo_height"
                                            value="{{ old('logo_height', $invoice->logo_height) }}" class="form-control">
                                    </div>

                                    <div class="col-md-4 form-group">
                                        <label><strong>{{ __('db.Logo Width') }}</strong></label>
                                        <input type="number" name="logo_width"
                                            value="{{ old('logo_width', $invoice->logo_width) }}" class="form-control">
                                    </div>

                                    <div class="col-md-4 form-group">
                                        <label><strong>{{ __('db.Primary Color') }}</strong></label>
                                        <input type="color" name="primary_color" id="colorPicker"
                                            value="{{ old('primary_color', $invoice->primary_color) }}"
                                            class="form-control">
                                    </div>


                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label
                                                for="invoice_date_format"><strong>{{ __('db.Invoice Date Format') }}</strong></label>
                                            <select name="invoice_date_format" id="invoice_date_format"
                                                class="form-control">
                                                <option value="d.m.y h:m A"
                                                    {{ $invoice->invoice_date_format == 'd.m.y h:m A' ? 'selected' : '' }}>
                                                    d.m.y h:m A</option>
                                                <option value="m.d.y h:m A"
                                                    {{ $invoice->invoice_date_format == 'm.d.y h:m A' ? 'selected' : '' }}>
                                                    m.d.y h:m A</option>
                                                <option value="y.m.d h:m A"
                                                    {{ $invoice->invoice_date_format == 'y.m.d h:m A' ? 'selected' : '' }}>
                                                    y.m.d h:m A</option>
                                                <option value="d-m-y h:m A"
                                                    {{ $invoice->invoice_date_format == 'd-m-y h:m A' ? 'selected' : '' }}>
                                                    d-m-y h:m A</option>
                                                <option value="y-m-d h:m A"
                                                    {{ $invoice->invoice_date_format == 'y-m-d h:m A' ? 'selected' : '' }}>
                                                    y-m-dd h:m A</option>
                                                <option value="d/m/y h:m A"
                                                    {{ $invoice->invoice_date_format == 'd/m/y h:m A' ? 'selected' : '' }}>
                                                    d/m/y h:m A</option>
                                            </select>
                                        </div>
                                    </div>



                                </div>

                                <div class="row">
                                    <input type="hidden" name="is_default"
                                        value="{{ $invoice->is_default == 1 ? 1 : 0 }}">

                                    @php
                                        $checkboxes = [
                                            'show_barcode' => 'db.Show Barcode',
                                            'show_qr_code' => 'db.Show QR Code',
                                            'show_description' => 'db.Show Description(Only 58mm,80mm)',
                                            'show_in_words' => 'db.Show In Words',
                                            'active_primary_color' => 'db.Active Primary Color',
                                            'show_warehouse_info' => 'db.Show Warehouse Info',
                                            'show_bill_to_info' => 'db.Show Bill TO Info',
                                            'show_biller_info' => 'db.Received By Info',
                                            'show_footer_text' => 'db.Show Footer Text',
                                            'show_payment_note' => 'db.Show Payment Note',
                                            'show_ref_number' => 'db.Show Reference No',
                                            'active_date_format' => 'db.Active Date Format',
                                            'active_generat_settings' => 'db.Invoice Generate Setting',
                                            'active_logo_height_width' => 'db.Active Logo Height Width',
                                        ];

                                        // Decode JSON if it's stored as string in DB
$show_column = old('show_column', $invoice->show_column ?? []);
                                        if (is_string($show_column)) {
                                            $show_column = json_decode($show_column, true);
                                        }
                                    @endphp
                                    <hr>
                                    <div class="col-md-12 mb-2">
                                        <label class="custom-checkbox">
                                            <input type="checkbox" id="select-all">
                                            <strong>{{ __('db.Select All') }}</strong>
                                        </label>
                                    </div>

                                    @foreach ($checkboxes as $field => $label)
                                        <div class="col-md-4">
                                            <div class="checkbox-item">
                                                <label class="custom-checkbox">
                                                    <input type="checkbox" name="show_column[{{ $field }}]"
                                                        value="1"
                                                        {{ isset($show_column[$field]) && $show_column[$field] ? 'checked' : '' }}>
                                                    {{-- <span class="checkmark"></span> --}}
                                                    <strong>{{ __($label) }}</strong>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach

                                </div>


                                <div class="form-group mt-3">
                                    <button type="submit" class="btn btn-primary">{{ __('db.update') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script type="text/javascript" src="{{ asset('/') }}vendor/jquery/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.numberingType').on('change', function() {
                var type = $(this).val();

                if (type == 'sequential') {
                    $('.digit_number').hide();
                    $('.start_number').show();
                } else if (type == 'random') {
                    $('.digit_number').show();
                    $('.start_number').hide();
                } else {
                    $('.digit_number').hide();
                    $('.start_number').hide();
                }
                console.log(type);
            });

            $('.numberingType').trigger('change');
        });

        $(document).ready(function() {
            $('input[name="number_of_digit"]').on('input', function() {
                var value = parseInt($(this).val(), 10);
                if (value < 6 || value > 12) {
                    $(this).css('border', '2px solid red');
                } else {
                    $(this).css('border', '2px solid green');
                }
            });
        });
        $('#select-all').on('change', function() {
            var isChecked = $(this).is(':checked');
            $('input[name^="show_column"]').prop('checked', isChecked);
        });


        $('#prefix').on('input', function() {
            var length = $(this).val().length;

            if (length == 0) {
                $('#prefix-message').text('').css('color', '');
            } else if (length < 2) {
                $('#prefix-message').text('Minimum 2 characters required.').css('color', 'red');
            } else if (length > 10) {
                $('#prefix-message').text('Maximum 10 characters allowed.').css('color', 'red');
            } else {
                $('#prefix-message').text('Good input ✅').css('color', 'green');
            }
        });

        $('.validate-input').on('input', function() {
            let $this = $(this);
            let min = parseInt($this.data('min'));
            let max = parseInt($this.data('max'));
            let target = $this.data('target');
            let value = $this.val();
            let message = '';
            let color = 'red';

            // If user enters more than max, block it and show error
            if (value.length > max) {
                $this.val(value.substring(0, max));
                message = `Maximum ${max} characters allowed.`;
            } else if (value.length < min) {
                message = `Must be at least ${min} characters.`;
            } else {
                message = 'Looks good!';
                color = 'green';
            }

            $(target).text(message).css('color', color);
        });
    </script>
@endsection

@push('script')
@endpush
