@extends('backend.layout.main')

@section('content')

@if(session('message'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('message') }}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
@endif
@if(session('not_permitted'))
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    {!! nl2br(e(session('not_permitted'))) !!}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
@endif
@if ($errors->any())
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <strong>Validation failed:</strong>
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
@endif

<x-error-message key="message" />
<x-error-message key="not_permitted" />

<section class="forms">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card shadow-sm">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h4 class="mb-0">{{ __('db.Import Purchase (Simple)') }}</h4>
            <div class="d-flex gap-2">
              <a href="{{ asset('sample_file/sample_purchase_products_simple.csv') }}" class="btn btn-primary btn-sm">
                <i class="dripicons-download"></i> {{ __('db.Download Sample File') }}
              </a>
              <a href="{{ asset('sample_file/sample_purchase_products_extended.csv') }}" class="btn btn-outline-primary btn-sm">
                <i class="dripicons-download"></i> Extended Sample
              </a>
            </div>
          </div>

          <div class="card-body">
            <p class="text-muted mb-3">
              <small>{{ __('db.The field labels marked with * are required input fields') }}.</small>
            </p>

            {!! Form::open(['route' => 'purchase.import', 'method' => 'post', 'files' => true, 'id' => 'purchase-form']) !!}

            <div class="row g-3">
              <div class="col-md-3">
                <label class="form-label">{{ __('db.Warehouse') }} *</label>
                <select required name="warehouse_id" class="selectpicker form-control" data-live-search="true" title="{{ __('db.Select warehouse') }}...">
                  @foreach($lims_warehouse_list as $warehouse)
                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                  @endforeach
                </select>
                @error('warehouse_id') <small class="text-danger">{{ $message }}</small> @enderror
              </div>

              <div class="col-md-3">
                <label class="form-label">{{ __('db.Customer') }}</label>
                <select name="customer_id" class="selectpicker form-control" data-live-search="true" title="{{ __('db.Select customer') }}...">
                  @foreach($lims_customer_list as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}{{ $customer->company_name ? ' ('.$customer->company_name.')' : '' }}</option>
                  @endforeach
                </select>
                <small class="text-muted">Saved to <strong>supplier_id</strong> on Purchase.</small>
              </div>

              <div class="col-md-3">
                <label class="form-label">PO No *</label>
                <input type="text" name="po_no" class="form-control" placeholder="Auto if empty" value="{{ old('po_no') }}">
                <small class="text-muted d-block">Auto-generate if left blank.</small>
                @error('po_no') <small class="text-danger">{{ $message }}</small> @enderror
              </div>

              <div class="col-md-3">
                <label class="form-label">{{ __('db.Purchase Status') }} *</label>
                <select name="status" class="form-control" required>
                  <option value="1" {{ old('status')=='1'?'selected':'' }}>{{__('db.Recieved')}}</option>
                  <option value="2" {{ old('status')=='2'?'selected':'' }}>{{__('db.Partial')}}</option>
                  <option value="3" {{ old('status')=='3'?'selected':'' }}>{{__('db.Pending')}}</option>
                  <option value="4" {{ old('status')=='4'?'selected':'' }}>{{__('db.Ordered')}}</option>
                  <option value="5" {{ old('status')=='5'?'selected':'' }}>{{__('In Process')}}</option>
                  <option value="6" {{ old('status')=='6'?'selected':'' }}>{{__('Cancel')}}</option>
                  <option value="7" {{ old('status')=='7'?'selected':'' }}>{{__('Complete')}}</option>
                </select>
                @error('status') <small class="text-danger">{{ $message }}</small> @enderror
              </div>
            </div>

            <div class="row g-3 mt-1">
              <div class="col-md-3">
                <label class="form-label">Reference No (auto if empty)</label>
                <input type="text" name="reference_no" class="form-control" placeholder="pr-YYYYMMDD-hhmmss" value="{{ old('reference_no') }}">
                <small class="text-muted d-block mt-1">Same Reference + Warehouse → rows will be <strong>appended</strong>.</small>
              </div>

              <div class="col-md-3">
                <label class="form-label">Created At</label>
                <input type="text" name="created_at" class="form-control date" placeholder="dd/mm/YYYY HH:MM (optional)" value="{{ old('created_at') }}">
              </div>

              <div class="col-md-3">
                <label class="form-label"><strong>{{ __('db.Discount') }}</strong></label>
                <input type="number" name="order_discount" class="form-control" step="any" value="{{ old('order_discount', 0) }}">
                @error('order_discount') <small class="text-danger">{{ $message }}</small> @enderror
              </div>
            </div>

            <div class="row g-3 mt-1">
              <div class="col-md-6">
                <label class="form-label">{{ __('db.Attach Document') }}</label>
                <i class="dripicons-question" data-toggle="tooltip"
                   title="Allowed: jpg, jpeg, png, gif, pdf, csv, docx, xlsx, txt"></i>
                <input type="file" name="document" class="form-control" />
                @error('document') <small class="text-danger">{{ $message }}</small> @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label">{{ __('db.Upload CSV File') }} *</label>
                <input type="file" name="file" class="form-control" required />
                <small class="text-muted d-block mt-1">
                  <strong>Required headers</strong> (order-free): <code>product_code, quantity</code>. Optional: <code>discount, batch_no, lot_no, expired_date, moq</code>.<br>
                  <em>Note:</em> Unit cost will be taken from <code>product.cost</code> automatically.
                </small>
                @error('file') <small class="text-danger d-block">{{ $message }}</small> @enderror
              </div>
            </div>

            <div class="row g-3 mt-1">
              <div class="col-md-4">
                <label class="form-label">{{ __('db.Note') }}</label>
                <textarea rows="3" class="form-control" name="note" placeholder="Internal note...">{{ old('note') }}</textarea>
              </div>
              <div class="col-md-4">
                <label class="form-label">Comments</label>
                <textarea rows="3" class="form-control" name="comments" placeholder="External/General comments...">{{ old('comments') }}</textarea>
              </div>
              <div class="col-md-4">
                <label class="form-label">Ship Instruction</label>
                <textarea rows="3" class="form-control" name="ship_instruction" placeholder="Any shipping instruction...">{{ old('ship_instruction') }}</textarea>
              </div>
            </div>

            <div class="mt-4">
              <button type="submit" class="btn btn-primary">
                {{ __('db.submit') }}
              </button>
            </div>

            {!! Form::close() !!}

            @if(session('line_errors') && is_array(session('line_errors')))
              <hr>
              <h6 class="text-danger mb-2">Row Errors</h6>
              <ul class="small text-danger mb-0">
                @foreach(session('line_errors') as $err)
                  <li>{!! nl2br(e($err)) !!}</li>
                @endforeach
              </ul>
            @endif

          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
  $("ul#purchase").siblings('a').attr('aria-expanded','true');
  $("ul#purchase").addClass("show");
  $("ul#purchase #purchase-import-menu").addClass("active");

  $('.selectpicker').selectpicker({ style: 'btn-link' });
  $('[data-toggle="tooltip"]').tooltip();
</script>
@endpush
