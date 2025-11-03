<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Biller;
use App\Models\Product;
use App\Models\Unit;
use App\Models\Tax;
use App\Models\Quotation;
use App\Models\Delivery;
use App\Models\PosSetting;
use App\Models\CustomField;
use App\Models\Currency;
use App\Models\Wproduction;
use App\Models\product_purchase_log;
use App\Models\ProductQuotation;
use App\Models\Product_Warehouse;
use App\Models\ProductVariant;
use App\Models\ProductBatch;
use App\Models\Variant;
use DB;
use NumberToWords\NumberToWords;
use Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Mail\QuotationDetails;
use Mail;
use Illuminate\Support\Facades\Validator;
use App\Models\MailSetting;
use App\Traits\MailInfo;
use App\Traits\StaffAccess;
use App\Traits\TenantInfo;

use PDF; 
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\File; // <-- yeh add karein
use Carbon\Carbon;

class QuotationController extends Controller
{
    use TenantInfo, MailInfo, StaffAccess;

    public function index(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('quotes-index')){
            if($request->input('warehouse_id'))
                $warehouse_id = $request->input('warehouse_id');
            else
                $warehouse_id = 0;

            if($request->input('starting_date')) {
                $starting_date = $request->input('starting_date');
                $ending_date = $request->input('ending_date');
            }
            else {
                $starting_date = date("Y-m-d", strtotime(date('Y-m-d', strtotime('-1 year', strtotime(date('Y-m-d') )))));
                $ending_date = date("Y-m-d");
            }

            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if(empty($all_permission))
                $all_permission[] = 'dummy text';

            $lims_warehouse_list = Warehouse::where('is_active', true)->get();

            /*if(Auth::user()->role_id > 2 && config('staff_access') == 'own')
                $lims_quotation_all = Quotation::with('biller', 'customer', 'supplier', 'user')->orderBy('id', 'desc')->where('user_id', Auth::id())->get();
            else
                $lims_quotation_all = Quotation::with('biller', 'customer', 'supplier', 'user')->orderBy('id', 'desc')->get();*/
            return view('backend.quotation.index', compact('lims_warehouse_list', 'all_permission', 'warehouse_id', 'starting_date', 'ending_date'));
        }
        else
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
    }

    public function quotationData(Request $request)
    {
        $columns = array(
            1 => 'created_at',
            2 => 'reference_no',
            5 => 'grand_total',
            6 => 'paid_amount',
        );

        $warehouse_id = $request->input('warehouse_id');
        if(Auth::user()->role_id > 2 && config('staff_access') == 'own')
            $totalData = Quotation::where('user_id', Auth::id())
                        ->whereDate('created_at', '>=' ,$request->input('starting_date'))
                        ->whereDate('created_at', '<=' ,$request->input('ending_date'))
                        ->count();
        //check staff access
        elseif(Auth::user()->role_id > 2 && config('staff_access') == 'warehouse')
            $totalData = Quotation::where('warehouse_id', Auth::user()->warehouse_id)
            ->whereDate('created_at', '>=' ,$request->input('starting_date'))
            ->whereDate('created_at', '<=' ,$request->input('ending_date'))
            ->count();
        elseif($warehouse_id != 0)
            $totalData = Quotation::where('warehouse_id', $warehouse_id)
                        ->whereDate('created_at', '>=' ,$request->input('starting_date'))
                        ->whereDate('created_at', '<=' ,$request->input('ending_date'))
                        ->count();
        elseif($warehouse_id != 0)
            $totalData = Quotation::where('warehouse_id', $warehouse_id)
                        ->whereDate('created_at', '>=' ,$request->input('starting_date'))
                        ->whereDate('created_at', '<=' ,$request->input('ending_date'))
                        ->count();
        else
            $totalData = Quotation::whereDate('created_at', '>=' ,$request->input('starting_date'))
                        ->whereDate('created_at', '<=' ,$request->input('ending_date'))
                        ->count();

        $totalFiltered = $totalData;

        if($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        if(empty($request->input('search.value'))) {
            if(Auth::user()->role_id > 2 && config('staff_access') == 'own')
                $quotations = Quotation::with('biller', 'customer', 'supplier', 'user')->offset($start)
                            ->where('user_id', Auth::id())
                            ->whereDate('created_at', '>=' ,$request->input('starting_date'))
                            ->whereDate('created_at', '<=' ,$request->input('ending_date'))
                            ->limit($limit)
                            ->orderBy($order, $dir)
                            ->get();
            elseif(Auth::user()->role_id > 2 && config('staff_access') == 'warehouse')
                $quotations = Quotation::with('biller', 'customer', 'supplier', 'user')->offset($start)
                            ->where('warehouse_id', Auth::user()->warehouse_id)
                            ->whereDate('created_at', '>=' ,$request->input('starting_date'))
                            ->whereDate('created_at', '<=' ,$request->input('ending_date'))
                            ->limit($limit)
                            ->orderBy($order, $dir)
                            ->get();
            elseif($warehouse_id != 0)
                $quotations = Quotation::with('biller', 'customer', 'supplier', 'user')->offset($start)
                            ->where('warehouse_id', $warehouse_id)
                            ->whereDate('created_at', '>=' ,$request->input('starting_date'))
                            ->whereDate('created_at', '<=' ,$request->input('ending_date'))
                            ->limit($limit)
                            ->orderBy($order, $dir)
                            ->get();
            else
                $quotations = Quotation::with('biller', 'customer', 'supplier', 'user')->offset($start)
                            ->whereDate('created_at', '>=' ,$request->input('starting_date'))
                            ->whereDate('created_at', '<=' ,$request->input('ending_date'))
                            ->limit($limit)
                            ->orderBy($order, $dir)
                            ->get();
        }
        else
        {
            $search = $request->input('search.value');
            if(Auth::user()->role_id > 2 && config('staff_access') == 'own') {
                $quotations =  Quotation::select('quotations.*')
                            ->with('biller', 'customer', 'supplier', 'user')
                            ->join('billers', 'quotations.biller_id', '=', 'billers.id')
                            ->join('customers', 'quotations.customer_id', '=', 'customers.id')
                            ->leftJoin('suppliers', 'quotations.supplier_id', '=', 'suppliers.id')
                            ->whereDate('quotations.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                            ->where('quotations.user_id', Auth::id())
                            ->orwhere([
                                ['quotations.reference_no', 'LIKE', "%{$search}%"],
                                ['quotations.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['billers.name', 'LIKE', "%{$search}%"],
                                ['quotations.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['customers.name', 'LIKE', "%{$search}%"],
                                ['quotations.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['suppliers.name', 'LIKE', "%{$search}%"],
                                ['quotations.user_id', Auth::id()]
                            ])
                            ->offset($start)
                            ->limit($limit)
                            ->orderBy($order,$dir)->get();

                $totalFiltered = Quotation::join('billers', 'quotations.biller_id', '=', 'billers.id')
                            ->join('customers', 'quotations.customer_id', '=', 'customers.id')
                            ->leftJoin('suppliers', 'quotations.supplier_id', '=', 'suppliers.id')
                            ->whereDate('quotations.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                            ->where('quotations.user_id', Auth::id())
                            ->orwhere([
                                ['quotations.reference_no', 'LIKE', "%{$search}%"],
                                ['quotations.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['billers.name', 'LIKE', "%{$search}%"],
                                ['quotations.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['customers.name', 'LIKE', "%{$search}%"],
                                ['quotations.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['suppliers.name', 'LIKE', "%{$search}%"],
                                ['quotations.user_id', Auth::id()]
                            ])
                            ->count();
            }
            elseif(Auth::user()->role_id > 2 && config('staff_access') == 'warehouse') {
                $quotations =  Quotation::select('quotations.*')
                            ->with('biller', 'customer', 'supplier', 'user')
                            ->join('billers', 'quotations.biller_id', '=', 'billers.id')
                            ->join('customers', 'quotations.customer_id', '=', 'customers.id')
                            ->leftJoin('suppliers', 'quotations.supplier_id', '=', 'suppliers.id')
                            ->whereDate('quotations.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                            ->where('quotations.user_id', Auth::id())
                            ->orwhere([
                                ['quotations.reference_no', 'LIKE', "%{$search}%"],
                                ['quotations.warehouse_id', Auth::user()->warehouse_id]
                            ])
                            ->orwhere([
                                ['billers.name', 'LIKE', "%{$search}%"],
                                ['quotations.warehouse_id', Auth::user()->warehouse_id]
                            ])
                            ->orwhere([
                                ['customers.name', 'LIKE', "%{$search}%"],
                                ['quotations.warehouse_id', Auth::user()->warehouse_id]
                            ])
                            ->orwhere([
                                ['suppliers.name', 'LIKE', "%{$search}%"],
                                ['quotations.warehouse_id', Auth::user()->warehouse_id]
                            ])
                            ->offset($start)
                            ->limit($limit)
                            ->orderBy($order,$dir)->get();

                $totalFiltered = Quotation::join('billers', 'quotations.biller_id', '=', 'billers.id')
                            ->join('customers', 'quotations.customer_id', '=', 'customers.id')
                            ->leftJoin('suppliers', 'quotations.supplier_id', '=', 'suppliers.id')
                            ->whereDate('quotations.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                            ->where('quotations.user_id', Auth::id())
                            ->orwhere([
                                ['quotations.reference_no', 'LIKE', "%{$search}%"],
                                ['quotations.warehouse_id', Auth::user()->warehouse_id]
                            ])
                            ->orwhere([
                                ['billers.name', 'LIKE', "%{$search}%"],
                                ['quotations.warehouse_id', Auth::user()->warehouse_id]
                            ])
                            ->orwhere([
                                ['customers.name', 'LIKE', "%{$search}%"],
                                ['quotations.warehouse_id', Auth::user()->warehouse_id]
                            ])
                            ->orwhere([
                                ['suppliers.name', 'LIKE', "%{$search}%"],
                                ['quotations.warehouse_id', Auth::user()->warehouse_id]
                            ])
                            ->count();
            }
            else {
                $quotations =  Quotation::select('quotations.*')
                            ->with('biller', 'customer', 'supplier', 'user')
                            ->join('billers', 'quotations.biller_id', '=', 'billers.id')
                            ->join('customers', 'quotations.customer_id', '=', 'customers.id')
                            ->leftJoin('suppliers', 'quotations.supplier_id', '=', 'suppliers.id')
                            ->whereDate('quotations.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                            ->orwhere('quotations.reference_no', 'LIKE', "%{$search}%")
                            ->orwhere('billers.name', 'LIKE', "%{$search}%")
                            ->orwhere('customers.name', 'LIKE', "%{$search}%")
                            ->orwhere('suppliers.name', 'LIKE', "%{$search}%")
                            ->offset($start)
                            ->limit($limit)
                            ->orderBy($order,$dir)
                            ->get();

                $totalFiltered = Quotation::join('billers', 'quotations.biller_id', '=', 'billers.id')
                            ->join('customers', 'quotations.customer_id', '=', 'customers.id')
                            ->leftJoin('suppliers', 'quotations.supplier_id', '=', 'suppliers.id')
                            ->whereDate('quotations.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                            ->orwhere('quotations.reference_no', 'LIKE', "%{$search}%")
                            ->orwhere('billers.name', 'LIKE', "%{$search}%")
                            ->orwhere('customers.name', 'LIKE', "%{$search}%")
                            ->orwhere('suppliers.name', 'LIKE', "%{$search}%")
                            ->count();
            }
        }
        $data = array();
        if(!empty($quotations))
        {
            foreach ($quotations as $key => $quotation)
            {
                $nestedData['id'] = $quotation->id;
                $nestedData['key'] = $key;
                $nestedData['date'] = date(config('date_format'), strtotime($quotation->created_at->toDateString()));
                $nestedData['reference_no'] = $quotation->reference_no;
                $nestedData['warehouse'] = $quotation->warehouse->company;
                $nestedData['biller'] = $quotation->biller->company_name;
                $nestedData['customer'] = $quotation->customer->company_name;


                $nestedData['total_qty'] = $quotation->total_qty;    
                
                if($quotation->quotation_status == 1) {
                    $nestedData['status'] = '<div class="badge badge-danger">'.__('db.Pending').'</div>';
                    $status = __('db.Pending');
                }
                else if ($quotation->quotation_status == 3) {
                    $nestedData['status'] = '<div class="badge badge-success">'.__('New').'</div>';
                   $status = __('New');   
                }
                 else if ($quotation->quotation_status == 4) {
                    $nestedData['status'] = '<div class="badge badge-secondary">'.__('Accepted').'</div>';
                   $status = __('Accepted');   
                    
                }
                elseif ($quotation->quotation_status == 5) {
                    $nestedData['status'] = '<div class="badge badge-info">'.__('In Progress').'</div>';
                   $status = __('In_Progress');   
                    
                }
                else if ($quotation->quotation_status == 6) {
                    $nestedData['status'] = '<div class="badge badge-warning">'.__('Cancel').'</div>';
                   $status = __('Cancel');   
                    
                }
                else{
                    $nestedData['status'] = '<div class="badge badge-primary">'.__('db.Sent').'</div>';
                    $status = __('db.Sent');
                }

                $nestedData['grand_total'] = number_format($quotation->grand_total, config('decimal'));
                $nestedData['options'] = '<div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.__("db.action").'
                              <span class="caret"></span>
                              <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                                <li>
                                    <button type="button" class="btn btn-link view"><i class="fa fa-eye"></i> '.__('db.View').'</button>
                                </li>';
                if(in_array("quotes-edit", $request['all_permission']))
                    $nestedData['options'] .= '<li>
                        <a href="'.route('quotations.edit', $quotation->id).'" class="btn btn-link"><i class="dripicons-document-edit"></i> '.__('db.edit').'</a>
                        </li>';
                $nestedData['options'] .= '<li>
                        <a href="'.route('quotation.create_sale', $quotation->id).'" class="btn btn-link"><i class="fa fa-shopping-cart"></i> '.__('db.Create Sale').'</a>
                        </li>';
                $nestedData['options'] .= '<li>
                        <a href="'.route('quotation.create_purchase', $quotation->id).'" class="btn btn-link"><i class="fa fa-shopping-basket"></i> '.__('db.Create Purchase').'</a>
                        </li>';
                if(in_array("quotes-delete", $request['all_permission']))
                    $nestedData['options'] .= \Form::open(["route" => ["quotations.destroy", $quotation->id], "method" => "DELETE"] ).'
                            <li>
                              <button type="submit" class="btn btn-link" onclick="return confirmDelete()"><i class="dripicons-trash"></i> '.__("db.delete").'</button>
                            </li>'.\Form::close().'
                        </ul>
                    </div>';

                // data for quotation details by one click

            $nestedData['quotation'] = array( '[ "'.date(config('date_format'), strtotime($quotation->created_at->toDateString())).'"',
            '"'.$quotation->reference_no.'"',
            ' "'.$status.'"',
            ' "'.$quotation->id.'"',
            ' "'.$quotation->biller->name.'"',
            ' "'.$quotation->biller->company_name.'"',
            ' "'.$quotation->biller->phone_number.'"',
            ' "'.$quotation->biller->address.'"',
            ' "'.$quotation->biller->email.'"',
            // ' "'.$quotation->biller->city.'"',
            ' "'.optional($quotation->customer)->name . '"',
            ' "'.optional($quotation->customer)->email . '"',
            ' "'.optional($quotation->customer)->phone_number . '"',
            ' "'.optional($quotation->customer)->company_name . '"',
            ' "'.optional($quotation->customer)->address . '"',
            ' "'.optional($quotation->customer)->web.'"',
            ' "'.$quotation->total_tax.'"',
            ' "'.$quotation->total_discount.'"',
            ' "'.$quotation->total_price.'"',
            ' "'.$quotation->order_tax.'"',
            ' "'.$quotation->order_tax_rate.'"',
            ' "'.$quotation->order_discount.'"',
            ' "'.$quotation->shipping_cost.'"',
            ' "'.$quotation->grand_total.'"',
            ' "'.preg_replace('/\s+/S', " ", $quotation->note).'"',
            ' "'.$quotation->user->name.'"', 
            ' "'.$quotation->user->email.'"',
            ' "'.$quotation->signature.'"',
            ' "' . optional($quotation->warehouse)->name . '"',
            ' "' . optional($quotation->warehouse)->phone . '"',
            ' "' . preg_replace('/\s+/S', " ", optional($quotation->warehouse)->address) . '"',
              ' "' . optional($quotation->warehouse)->company . '"',
               ' "' . optional($quotation->warehouse)->web . '"',
               ' "' .$quotation->qt_no. '"',
            ' "'.$quotation->document.'"]'
            );
            $data[] = $nestedData;
          }
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );

        echo json_encode($json_data);
    }

// public function create()
//     {
//         $role = Role::find(Auth::user()->role_id);
//         if($role->hasPermissionTo('quotes-add')){
//             $lims_biller_list = Biller::where('is_active', true)->get();
//             $lims_warehouse_list = Warehouse::where('is_active', true)->get();
//             $lims_customer_list = Customer::where('is_active', true)->get();
//             $lims_supplier_list = Supplier::where('is_active', true)->get();
//             $lims_tax_list = Tax::where('is_active', true)->get();

//             return view('backend.quotation.create', compact('lims_biller_list', 'lims_warehouse_list', 'lims_customer_list', 'lims_supplier_list', 'lims_tax_list'));
//         }
//         else
//             return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
//     }

    public function create()
    {
         $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('purchases-add')){
            $lims_supplier_list = Supplier::where('is_active', true)->get();
             $lims_customer_list = Customer::with('user')->get();
             $lims_biller_list = Biller::where('is_active', true)->get();
            if(Auth::user()->role_id > 2) 
            {
                $lims_customer_list = Customer::with('user')
                ->where('user_id', Auth::user()->id)
                ->whereHas('user', function ($query) {
                $query->where('role_id', Auth::user()->role_id);
                })
                ->get();
            }


                $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_tax_list = Tax::where('is_active', true)->get();
        $lims_product_list_without_variant = $this->productWithoutVariant_();
        $lims_product_list_with_variant = $this->productWithVariant_();
        $currency_list = Currency::where('is_active', true)->get();
       
            return view('backend.quotation.create', compact('lims_customer_list','lims_supplier_list', 'lims_warehouse_list', 'lims_tax_list', 'lims_product_list_without_variant', 'lims_product_list_with_variant', 'currency_list','lims_biller_list' ));
        }
        else
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
    }

    public function store(Request $request)
    { 
        $data = $request->except('document');
        // return dd($data);
        $data['user_id'] = Auth::id();
        $document = $request->document;
        if($document){
            $v = Validator::make(
                [
                    'extension' => strtolower($request->document->getClientOriginalExtension()),
                ],
                [
                    'extension' => 'in:jpg,jpeg,png,gif,pdf,csv,docx,xlsx,txt',
                ]
            );
            if ($v->fails())
                return redirect()->back()->withErrors($v->errors());
            $ext = pathinfo($document->getClientOriginalName(), PATHINFO_EXTENSION);
            $documentName = date("Ymdhis");
            if(!config('database.connections.saleprosaas_landlord')) {
                $documentName = $documentName . '.' . $ext;
                $document->move(public_path('documents/quotation'), $documentName);
            }
            else {
                $documentName = $this->getTenantId() . '_' . $documentName . '.' . $ext;
                $document->move(public_path('documents/quotation'), $documentName);
            }
            $data['document'] = $documentName;
        }
        
         if(!isset($data['reference_no']))
            {
            $data['reference_no'] = 'EZ-QT-' . date("Ymd") . '-'. date("his");
            }
            $data['qt_no'] = 'EZ-QT-' . date("Ymd") . '-'. date("his");
            if(isset($data['created_at'])) {
                $data['created_at'] = str_replace("/","-",$data['created_at']);
                $data['created_at'] = date("Y-m-d H:i:s", strtotime($data['created_at']));
            }
            else
            {
                $data['created_at'] = date("Y-m-d H:i:s");
            }

         $data['total_price'] = $data['total_cost'];
         $data['quotation_status'] = $data['status'];
        $lims_quotation_data = Quotation::create($data);
        if($lims_quotation_data->quotation_status == 2){
            //collecting mail data
            $lims_customer_data = Customer::find($data['customer_id']);
            $mail_data['email'] = $lims_customer_data->email;
            $mail_data['reference_no'] = $lims_quotation_data->reference_no;
            $mail_data['total_qty'] = $lims_quotation_data->total_qty;
            $mail_data['total_price'] = $lims_quotation_data->total_price;
            $mail_data['order_tax'] = $lims_quotation_data->order_tax;
            $mail_data['order_tax_rate'] = $lims_quotation_data->order_tax_rate;
            $mail_data['order_discount'] = $lims_quotation_data->order_discount;
            $mail_data['shipping_cost'] = $lims_quotation_data->shipping_cost;
            $mail_data['grand_total'] = $lims_quotation_data->grand_total;
        }
        $product_id = $data['product_id'];
        //$product_batch_id = $data['product_batch_id'];
        $product_code = $data['product_code'];
        $qty = $data['qty'];
        $supplier_ids = $data['supplier_name'];
        $ets_date = $data['ets_date'];
        $eta_date = $data['eta_date'];
        $lt_date = $data['lt_date'];
        $moq = $data['moq'];
         $ship_cost = $data['ship_cost'];
        $sale_unit = $data['sale_unit'];
        $net_unit_price = $data['net_unit_price'];
        $discount = $data['discount'];
        $tax_rate = $data['tax_rate'];
        $tax = $data['tax'];
        $total = $data['subtotal'];
        $product_quotation = [];

        foreach ($product_id as $i => $id) {

            if($sale_unit[$i] != 'n/a'){
                $lims_sale_unit_data = Unit::where('unit_name', $sale_unit[$i])->first();
                $sale_unit_id = $lims_sale_unit_data->id;
            }
            else
                $sale_unit_id = 0;
            if($sale_unit_id)
                $mail_data['unit'][$i] = $lims_sale_unit_data->unit_code;
            else
                $mail_data['unit'][$i] = '';
            $lims_product_data = Product::find($id);
            if($lims_product_data->is_variant) {
                $lims_product_variant_data = ProductVariant::select('variant_id')->FindExactProductWithCode($id, $product_code[$i])->first();
                $product_quotation['variant_id'] = $lims_product_variant_data->variant_id;
            }
            else
                $product_quotation['variant_id'] = null;
            if($product_quotation['variant_id']){
                $variant_data = Variant::find($product_quotation['variant_id']);
                $mail_data['products'][$i] = $lims_product_data->name . ' [' . $variant_data->name .']';
            }
            else
                $mail_data['products'][$i] = $lims_product_data->name;
            $product_quotation['quotation_id'] = $lims_quotation_data->id ;
            $product_quotation['product_id'] = $id;
            //$product_quotation['product_batch_id'] = $product_batch_id[$i];
            $product_quotation['qty'] = $mail_data['qty'][$i] = $qty[$i];
            $product_quotation['sale_unit_id'] = $sale_unit_id;
            $product_quotation['net_unit_price'] = $net_unit_price[$i];
            $product_quotation['discount'] = $discount[$i];
            $product_quotation['tax_rate'] = $tax_rate[$i];
            $product_quotation['tax'] = $tax[$i];
            $product_quotation['total'] = $mail_data['total'][$i] = $total[$i];
            $product_quotation['supplier_id'] = $supplier_ids[$i];

    $product_quotation['eta_date'] = date('Y-m-d', strtotime($eta_date[$i]));
    $product_quotation['ets_date'] = date('Y-m-d', strtotime($ets_date[$i]));
    $product_quotation['lt_date'] = $lt_date[$i];
                $product_quotation['moq'] = $moq[$i];
                $product_quotation['ship_cost'] = $ship_cost[$i];
            ProductQuotation::create($product_quotation);
        }
        $message = 'Quotation created successfully';
        $mail_setting = MailSetting::latest()->first();
        if($lims_quotation_data->quotation_status == 2 && $mail_data['email'] && $mail_setting) {
            $this->setMailInfo($mail_setting);
            try{
                Mail::to($mail_data['email'])->send(new QuotationDetails($mail_data));
            }
            catch(\Exception $e){
                $message = 'Quotation created successfully. Please setup your <a href="setting/mail_setting">mail setting</a> to send mail.';
            }
        }
        return redirect('quotations')->with('message', $message);
    }

    public function sendMail(Request $request)
    {
        $data = $request->all();
        $lims_quotation_data = Quotation::find($data['quotation_id']);
        $lims_product_quotation_data = ProductQuotation::where('quotation_id', $data['quotation_id'])->get();
        $lims_customer_data = Customer::find($lims_quotation_data->customer_id);
        $mail_setting = MailSetting::latest()->first();

        if(!$mail_setting) {
            $message = 'Please setup your <a href="setting/mail_setting">mail setting</a> to send mail.';
        }else if(!$lims_customer_data->email) {
            $message = 'Customer doesnt have email!';
        }
        else if($lims_customer_data->email && $mail_setting) {
            //collecting male data
            $mail_data['email'] = $lims_customer_data->email;
            $mail_data['reference_no'] = $lims_quotation_data->reference_no;
            $mail_data['total_qty'] = $lims_quotation_data->total_qty;
            $mail_data['total_price'] = $lims_quotation_data->total_price;
            $mail_data['order_tax'] = $lims_quotation_data->order_tax;
            $mail_data['order_tax_rate'] = $lims_quotation_data->order_tax_rate;
            $mail_data['order_discount'] = $lims_quotation_data->order_discount;
            $mail_data['shipping_cost'] = $lims_quotation_data->shipping_cost;
            $mail_data['grand_total'] = $lims_quotation_data->grand_total;

            foreach ($lims_product_quotation_data as $key => $product_quotation_data) {
                $lims_product_data = Product::find($product_quotation_data->product_id);
                if($product_quotation_data->variant_id) {
                    $variant_data = Variant::find($product_quotation_data->variant_id);
                    $mail_data['products'][$key] = $lims_product_data->name . ' [' . $variant_data->name . ']';
                }
                else
                    $mail_data['products'][$key] = $lims_product_data->name;
                if($product_quotation_data->sale_unit_id){
                    $lims_unit_data = Unit::find($product_quotation_data->sale_unit_id);
                    $mail_data['unit'][$key] = $lims_unit_data->unit_code;
                }
                else
                    $mail_data['unit'][$key] = '';

                $mail_data['qty'][$key] = $product_quotation_data->qty;
                $mail_data['total'][$key] = $product_quotation_data->total;
            }
            $this->setMailInfo($mail_setting);
            try{
                Mail::to($mail_data['email'])->send(new QuotationDetails($mail_data));
                $message = 'Mail sent successfully';
            }
            catch(\Exception $e){
                $message = 'Please setup your <a href="setting/mail_setting">mail setting</a> to send mail.';
            }
        }

        return redirect()->back()->with('message', $message);
    }

    public function getCustomerGroup($id)
    {
         $lims_customer_data = Customer::find($id);
         $lims_customer_group_data = CustomerGroup::find($lims_customer_data->customer_group_id);
         return $lims_customer_group_data->percentage;
    }

    // public function getProduct($id)
    // {
    //     $product_code = [];
    //     $product_name = [];
    //     $product_qty = [];
    //     $product_price = [];
    //     $product_data = [];
    //     $batch_no = [];
    //     $product_batch_id = [];
    //     $expired_date = [];
    //     $is_embeded = [];
    //     $imei_number = [];

    //     //retrieve data of product without variant
    //     $lims_product_warehouse_data = Product::join('product_warehouse', 'products.id', '=', 'product_warehouse.product_id')
    //     ->where([
    //         ['products.is_active', true],
    //         ['product_warehouse.warehouse_id', $id],
    //     ])
    //     ->whereNull('product_warehouse.variant_id')
    //     ->whereNull('product_warehouse.product_batch_id')
    //     ->select('product_warehouse.*')
    //     ->get();

    //     foreach ($lims_product_warehouse_data as $product_warehouse)
    //     {
    //         $product_qty[] = $product_warehouse->qty;
    //         $product_price[] = $product_warehouse->price;
    //         $lims_product_data = Product::find($product_warehouse->product_id);
    //         $product_code[] =  $lims_product_data->code;
    //         $product_name[] = $lims_product_data->name;
    //         $product_type[] = $lims_product_data->type;
    //         $product_id[] = $lims_product_data->id;
    //         $product_list[] = null;
    //         $qty_list[] = null;
    //         $batch_no[] = null;
    //         $product_batch_id[] = null;
    //         $expired_date[] = null;
    //         if($product_warehouse->is_embeded)
    //             $is_embeded[] = $product_warehouse->is_embeded;
    //         else
    //             $is_embeded[] = 0;
    //         $imei_number[] = null;
    //     }

    //     $lims_product_with_imei_warehouse_data = Product::join('product_warehouse', 'products.id', '=', 'product_warehouse.product_id')
    //     ->where([
    //         ['products.is_active', true],
    //         ['products.is_imei', true],
    //         ['product_warehouse.warehouse_id', $id],
    //         ['product_warehouse.qty', '>', 0]
    //     ])
    //     ->whereNull('product_warehouse.variant_id')
    //     ->whereNotNull('product_warehouse.imei_number')
    //     ->select('product_warehouse.*', 'products.is_embeded')
    //     ->groupBy('product_warehouse.product_id')
    //     ->get();

    //     config()->set('database.connections.mysql.strict', false);
    //     \DB::reconnect(); //important as the existing connection if any would be in strict mode

    //     $lims_product_with_batch_warehouse_data = Product::join('product_warehouse', 'products.id', '=', 'product_warehouse.product_id')
    //     ->where([
    //         ['products.is_active', true],
    //         ['product_warehouse.warehouse_id', $id],
    //     ])
    //     ->whereNull('product_warehouse.variant_id')
    //     ->whereNotNull('product_warehouse.product_batch_id')
    //     ->select('product_warehouse.*')
    //     ->groupBy('product_warehouse.product_id')
    //     ->get();

    //     //now changing back the strict ON
    //     config()->set('database.connections.mysql.strict', true);
    //     \DB::reconnect();

    //     foreach ($lims_product_with_batch_warehouse_data as $product_warehouse)
    //     {
    //         $product_qty[] = $product_warehouse->qty;
    //         $product_price[] = $product_warehouse->price;
    //         $lims_product_data = Product::find($product_warehouse->product_id);
    //         $product_code[] =  $lims_product_data->code;
    //         $product_name[] = $lims_product_data->name;
    //         $product_type[] = $lims_product_data->type;
    //         $product_id[] = $lims_product_data->id;
    //         $product_list[] = null;
    //         $qty_list[] = null;
    //         $product_batch_data = ProductBatch::select('id', 'batch_no')->find($product_warehouse->product_batch_id);
    //         $batch_no[] = $product_batch_data->batch_no;
    //         $product_batch_id[] = $product_batch_data->id;
    //         $expired_date[] = null;
    //         if($product_warehouse->is_embeded)
    //             $is_embeded[] = $product_warehouse->is_embeded;
    //         else
    //             $is_embeded[] = 0;
    //         $imei_number[] = null;
    //     }

    //       //product with imei
    //       foreach ($lims_product_with_imei_warehouse_data as $product_warehouse)
    //       {
    //           $imei_numbers = explode(",", $product_warehouse->imei_number);
    //           foreach ($imei_numbers as $key => $number) {
    //               $product_qty[] = $product_warehouse->qty;
    //               $product_price[] = $product_warehouse->price;
    //               $lims_product_data = Product::find($product_warehouse->product_id);
    //               $product_code[] =  $lims_product_data->code;
    //               $product_name[] = htmlspecialchars($lims_product_data->name);
    //               $product_type[] = $lims_product_data->type;
    //               $product_id[] = $lims_product_data->id;
    //               $product_list[] = $lims_product_data->product_list;
    //               $qty_list[] = $lims_product_data->qty_list;
    //               $batch_no[] = null;
    //               $product_batch_id[] = null;
    //               $expired_date[] = null;
    //               $is_embeded[] = 0;
    //               $imei_number[] = $number;
    //           }
    //       }

    //     //retrieve data of product with variant
    //     $lims_product_warehouse_data = Product::join('product_warehouse', 'products.id', '=', 'product_warehouse.product_id')
    //     ->where([
    //         ['products.is_active', true],
    //         ['product_warehouse.warehouse_id', $id],
    //     ])->whereNotNull('product_warehouse.variant_id')->select('product_warehouse.*')->get();
    //     foreach ($lims_product_warehouse_data as $product_warehouse)
    //     {
    //         $lims_product_data = Product::find($product_warehouse->product_id);
    //         $lims_product_variant_data = ProductVariant::select('item_code')->FindExactProduct($product_warehouse->product_id, $product_warehouse->variant_id)->first();
    //         if($lims_product_variant_data) {
    //             $product_qty[] = $product_warehouse->qty;
    //             $product_code[] =  $lims_product_variant_data->item_code;
    //             $product_name[] = $lims_product_data->name;
    //             $product_type[] = $lims_product_data->type;
    //             $product_id[] = $lims_product_data->id;
    //             $product_list[] = null;
    //             $qty_list[] = null;
    //             $batch_no[] = null;
    //             $product_batch_id[] = null;
    //         }
    //         $expired_date[] = null;
    //         if($product_warehouse->is_embeded)
    //             $is_embeded[] = $product_warehouse->is_embeded;
    //         else
    //             $is_embeded[] = 0;
    //         $imei_number[] = null;
    //     }
    //     //retrieve product data of digital and combo
    //     $lims_product_data = Product::whereNotIn('type', ['standard'])->where('is_active', true)->get();
    //     foreach ($lims_product_data as $product)
    //     {
    //         $product_qty[] = $product->qty;
    //         $product_code[] =  $product->code;
    //         $product_name[] = $product->name;
    //         $product_type[] = $product->type;
    //         $product_id[] = $product->id;
    //         $product_list[] = $product->product_list;
    //         $lims_product_data = $product->id;
    //         $qty_list[] = $product->qty_list;
    //         $expired_date[] = null;
    //         $is_embeded[] = 0;
    //         $imei_number[] = null;
    //     }
    //     $product_data = [$product_code, $product_name, $product_qty, $product_type, $product_id, $product_list, $qty_list, $product_price, $batch_no, $product_batch_id, $expired_date, $is_embeded, $imei_number];
    //     return $product_data;
    // }


     public function getProduct($id)
    {
        $product_id = [];
        $product_code = [];
        $product_name = [];
        $product_list = [];
        $product_qty = [];
        $product_price = [];
        $product_data = [];
        $batch_no = [];
        $product_batch_id = [];
        $expired_date = [];
        $is_embeded = [];
        $imei_number = [];
        $product_type = [];
        $qty_list = [];
       

        

        config()->set('database.connections.mysql.strict', false);
        \DB::reconnect(); //important as the existing connection if any would be in strict mode

    

        //retrieve product data of digital and combo
        // $lims_product_data = Product::whereNotIn('type', ['standard'])->where('is_active', true)->get();
        $lims_product_data = Product::where('is_active', true)->get();
        foreach ($lims_product_data as $product)
        {
            $product_id[] = $product->id;
            $product_qty[] = $product->qty;
            $product_code[] =  $product->code;
            $product_name[] = $product->name;
            $product_type[] = $product->type;
            $product_id[] = $product->id;
            $product_list[] = $product->product_list;
            $lims_product_data = $product->id;
            $qty_list[] = $product->qty_list;
            $expired_date[] = null;
            $is_embeded[] = 0;
            $imei_number[] = null;
        }
        $product_data = [$product_code, $product_name, $product_qty, $product_type, $product_id, $product_list, $qty_list, $product_price, $batch_no, $product_batch_id, $expired_date, $is_embeded, $imei_number];
        return $product_data;
    }


    // public function limsProductSearch(Request $request)
    // {
    //     $todayDate = date('Y-m-d');
    //     $product_data = explode("|", $request['data']);
    //     // $product_code = explode("(", $request['data']);
    //     $product_info = explode("?", $request['data']);
    //     $customer_id = $product_info[1];
    //     // if(strpos($request['data'], '|')) {
    //     //     $product_info = explode("|", $request['data']);
    //     //     $embeded_code = $product_code[0];
    //     //     $product_code[0] = substr($embeded_code, 0, 7);
    //     //     $qty = substr($embeded_code, 7, 5) / 1000;
    //     // }
    //     // else {
    //     //     $product_code[0] = rtrim($product_code[0], " ");
    //     //     $qty = $product_info[2];
    //     // }
    //     if($product_data[3][0]) {
    //         $product_info = explode("|", $request['data']);
    //         $embeded_code = $product_data[0];
    //         $product_data[0] = substr($embeded_code, 0, 7);
    //         $qty = substr($embeded_code, 7, 5) / 1000;
    //     }
    //     else {
    //         $qty = $product_info[2];
    //     }
    //     $product_variant_id = null;
    //     $all_discount = DB::table('discount_plan_customers')
    //                     ->join('discount_plans', 'discount_plans.id', '=', 'discount_plan_customers.discount_plan_id')
    //                     ->join('discount_plan_discounts', 'discount_plans.id', '=', 'discount_plan_discounts.discount_plan_id')
    //                     ->join('discounts', 'discounts.id', '=', 'discount_plan_discounts.discount_id')
    //                     ->where([
    //                         ['discount_plans.is_active', true],
    //                         ['discounts.is_active', true],
    //                         ['discount_plan_customers.customer_id', $customer_id]
    //                     ])
    //                     ->select('discounts.*')
    //                     ->get();
    //     // return $product_data[0];
    //     $lims_product_data = Product::where([
    //         ['code', $product_data[0]],
    //         ['is_active', true]
    //     ])->first();

    //     if(!$lims_product_data) {
    //         $lims_product_data = Product::join('product_variants', 'products.id', 'product_variants.product_id')
    //             ->select('products.*', 'product_variants.id as product_variant_id', 'product_variants.item_code', 'product_variants.additional_price')
    //             ->where([
    //                 ['product_variants.item_code', $product_data[0]],
    //                 ['products.is_active', true]
    //             ])->first();

    //         return $lims_product_data;
    //         $product_variant_id = $lims_product_data->product_variant_id;
    //     }

    //     $product[] = $lims_product_data->name;
    //     if($lims_product_data->is_variant){
    //         $product[] = $lims_product_data->item_code;
    //         $lims_product_data->price += $lims_product_data->additional_price;
    //     }
    //     else
    //         $product[] = $lims_product_data->code;

    //     $no_discount = 1;
    //     foreach ($all_discount as $key => $discount) {
    //         $product_list = explode(",", $discount->product_list);
    //         $days = explode(",", $discount->days);

    //         if( ( $discount->applicable_for == 'All' || in_array($lims_product_data->id, $product_list) ) && ( $todayDate >= $discount->valid_from && $todayDate <= $discount->valid_till && in_array(date('D'), $days) && $qty >= $discount->minimum_qty && $qty <= $discount->maximum_qty ) ) {
    //             if($discount->type == 'flat') {
    //                 $product[] = $lims_product_data->price - $discount->value;
    //             }
    //             elseif($discount->type == 'percentage') {
    //                 $product[] = $lims_product_data->price - ($lims_product_data->price * ($discount->value/100));
    //             }
    //             $no_discount = 0;
    //             break;
    //         }
    //         else {
    //             continue;
    //         }
    //     }

    //     if($lims_product_data->promotion && $todayDate <= $lims_product_data->last_date && $no_discount) {
    //         $product[] = $lims_product_data->promotion_price;
    //     }
    //     elseif($no_discount)
    //         $product[] = $lims_product_data->price;

    //     if($lims_product_data->tax_id) {
    //         $lims_tax_data = Tax::find($lims_product_data->tax_id);
    //         $product[] = $lims_tax_data->rate;
    //         $product[] = $lims_tax_data->name;
    //     }
    //     else{
    //         $product[] = 0;
    //         $product[] = 'No Tax';
    //     }
    //     $product[] = $lims_product_data->tax_method;
    //     if($lims_product_data->type == 'standard'){
    //         $units = Unit::where("base_unit", $lims_product_data->unit_id)
    //                 ->orWhere('id', $lims_product_data->unit_id)
    //                 ->get();
    //         $unit_name = array();
    //         $unit_operator = array();
    //         $unit_operation_value = array();
    //         foreach ($units as $unit) {
    //             if($lims_product_data->sale_unit_id == $unit->id) {
    //                 array_unshift($unit_name, $unit->unit_name);
    //                 array_unshift($unit_operator, $unit->operator);
    //                 array_unshift($unit_operation_value, $unit->operation_value);
    //             }
    //             else {
    //                 $unit_name[]  = $unit->unit_name;
    //                 $unit_operator[] = $unit->operator;
    //                 $unit_operation_value[] = $unit->operation_value;
    //             }
    //         }
    //         $product[] = implode(",",$unit_name) . ',';
    //         $product[] = implode(",",$unit_operator) . ',';
    //         $product[] = implode(",",$unit_operation_value) . ',';
    //     }
    //     else{
    //         $product[] = 'n/a'. ',';
    //         $product[] = 'n/a'. ',';
    //         $product[] = 'n/a'. ',';
    //     }
    //     $product[] = $lims_product_data->id;
    //     $product[] = $product_variant_id;
    //     $product[] = $lims_product_data->promotion;
    //     $product[] = $lims_product_data->is_batch;
    //     $product[] = $lims_product_data->is_imei;
    //     $product[] = $lims_product_data->is_variant;
    //     $product[] = $qty;
    //     $product[] = $lims_product_data->wholesale_price;
    //     $product[] = $lims_product_data->cost;
    //     $product[] = $product_data[2];

    //     return $product;
    // }


public function limsProductSearch(Request $request)
{
    // ---- Parse incoming value safely (supports "CODE", "CODE (NAME)", "CODE|NAME") ----
    $raw = trim((string)$request->input('data', ''));
    if ($raw === '') {
        return response()->json(['error' => 'Empty query'], 422);
    }

    // Prefer "CODE" part only
    // 1) split by ' (' if present
    $codeOnly = $raw;
    if (strpos($codeOnly, ' (') !== false) {
        $codeOnly = substr($codeOnly, 0, strpos($codeOnly, ' ('));
    }
    // 2) split by pipe if present
    if (strpos($codeOnly, '|') !== false) {
        $parts = explode('|', $codeOnly, 2);
        $codeOnly = trim($parts[0]);
    }
    $codeOnly = rtrim($codeOnly);

    // ---- Try VARIANT first (product_variants.item_code = code) ----
    $pv = Product::query()
        ->join('product_variants as pv', 'products.id', '=', 'pv.product_id')
        ->where('pv.item_code', $codeOnly)
        ->where('products.is_active', true)
        ->select([
            'products.*',
            'pv.item_code as pv_item_code',
            'pv.additional_cost as pv_additional_cost',
        ])
        ->first();

    if ($pv) {
        $productModel = $pv; // keep same variable name
        // Adjust cost with additional_cost (null-safe)
        $baseCost = (float)($productModel->cost ?? 0);
        $addCost  = (float)($productModel->pv_additional_cost ?? 0);
        $effectiveCost = $baseCost + $addCost;

        $codeToReturn = $productModel->pv_item_code; // variant code
    } else {
        // ---- Fallback: non-variant product by products.code ----
        $productModel = Product::query()
            ->where('code', $codeOnly)
            ->where('is_active', true)
            ->first();

        if (!$productModel) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $effectiveCost = (float)($productModel->cost ?? 0);
        $codeToReturn  = $productModel->code;
    }

    // ---- Tax info ----
    $taxRate = 0;
    $taxName = 'No Tax';
    if (!empty($productModel->tax_id)) {
        if ($tax = Tax::find($productModel->tax_id)) {
            $taxRate = (float)$tax->rate;
            $taxName = (string)$tax->name;
        }
    }

    // ---- Units (purchase unit first, then others of same base) ----
    $baseUnitId = $productModel->unit_id;
    $purchaseUnitId = $productModel->purchase_unit_id ?: $baseUnitId;

    $units = Unit::query()
        ->where('base_unit', $baseUnitId)
        ->orWhere('id', $baseUnitId)
        ->get();

    $unit_name = [];
    $unit_operator = [];
    $unit_operation_value = [];

    foreach ($units as $u) {
        if ((int)$purchaseUnitId === (int)$u->id) {
            array_unshift($unit_name, $u->unit_name);
            array_unshift($unit_operator, $u->operator);
            array_unshift($unit_operation_value, $u->operation_value);
        } else {
            $unit_name[] = $u->unit_name;
            $unit_operator[] = $u->operator;
            $unit_operation_value[] = $u->operation_value;
        }
    }

    // Safety: if no units found (edge case), at least push one neutral unit
    if (empty($unit_name)) {
        $unit_name = ['n/a'];
        $unit_operator = ['*'];
        $unit_operation_value = [1];
    }

    // ---- Build response in the exact order your JS expects ----
    $product = [];
    $product[] = (string)$productModel->name;                                  // [0] name
    $product[] = (string)$codeToReturn;                                        // [1] code or variant item_code
    $product[] = (float)$effectiveCost;                                        // [2] cost
    $product[] = (float)$taxRate;                                              // [3] tax rate
    $product[] = (string)$taxName;                                             // [4] tax name
    $product[] = (int)($productModel->tax_method ?? 1);                        // [5] tax method (1=exclusive,2=inclusive)
    $product[] = implode(',', $unit_name) . ',';                               // [6] unit names
    $product[] = implode(',', $unit_operator) . ',';                           // [7] unit operators
    $product[] = implode(',', $unit_operation_value) . ',';                    // [8] unit op values
    $product[] = (int)$productModel->id;                                       // [9] product id
    $product[] = (int)($productModel->is_batch ?? 0);                          // [10] is_batch
    $product[] = (int)($productModel->is_imei ?? 0);                           // [11] is_imei

    return response()->json($product);
}


    // public function productQuotationData($id)
    // {
    //     $lims_product_quotation_data = ProductQuotation::where('quotation_id', $id)->get();
    //     foreach ($lims_product_quotation_data as $key => $product_quotation_data) {
    //         $product = Product::find($product_quotation_data->product_id);
    //         if($product_quotation_data->variant_id) {
    //             $lims_product_variant_data = ProductVariant::select('item_code')->FindExactProduct($product_quotation_data->product_id, $product_quotation_data->variant_id)->first();
    //             $product->code = $lims_product_variant_data->item_code;
    //         }
    //         if($product_quotation_data->sale_unit_id){
    //             $unit_data = Unit::find($product_quotation_data->sale_unit_id);
    //             $unit = $unit_data->unit_code;
    //         }
    //         else
    //             $unit = '';

    // $product_quotation[0][$key] = $product->name . ' [' . $product->code . ']';
    //         $product_quotation[1][$key] = $product_quotation_data->qty;
    //         $product_quotation[2][$key] = $unit;
    //         $product_quotation[3][$key] = $product_quotation_data->tax;
    //         $product_quotation[4][$key] = $product_quotation_data->tax_rate;
    //         $product_quotation[5][$key] = $product_quotation_data->discount;
    //         $product_quotation[6][$key] = $product_quotation_data->total;
    //         if($product_quotation_data->product_batch_id) {
    //             $product_batch_data = ProductBatch::select('batch_no')->find($product_quotation_data->product_batch_id);
    //             $product_quotation[7][$key] = $product_batch_data->batch_no;
    //         }
    //         else
    //             $product_quotation[7][$key] = 'N/A';
    //     }
    //     return $product_quotation;
    // }



    public function productQuotationData($id)
    {
         // echo "<pre>";
         // echo $id;
         // echo "</pre>";

         // return 1;
        try {

            $lims_product_purchase_data = ProductQuotation::where('quotation_id', $id)->get();
            $product_purchase = [];
    foreach ($lims_product_purchase_data as $key => $product_purchase_data) 
            {
                $product = Product::find($product_purchase_data->product_id);
        $supplier = Supplier::find($product_purchase_data->supplier_id);
                $unit = Unit::find($product_purchase_data->sale_unit_id);
        $product_purchase[0][$key] = $product->id . ' [' . $product->name.']';
                $product_purchase[1][$key] = $product_purchase_data->qty;
                $product_purchase[2][$key] = $unit->unit_code;
                $product_purchase[3][$key] = $product_purchase_data->tax;
                $product_purchase[4][$key] = $product_purchase_data->tax_rate;
                $product_purchase[5][$key] = $product_purchase_data->ship_cost;
                $product_purchase[6][$key] = $product_purchase_data->total;
              
                $product_purchase[9][$key] = $supplier;
                $product_purchase[11][$key] = $product_purchase_data->moq;
                $product_purchase[12][$key] = $product->name;
                    
                   
                    // ✅ Check and add unique supplier based on ID
                    // $alreadyExists = false;
                    // foreach ($product_purchase[9] ?? [] as $existingSupplier) {
                    // if ($existingSupplier->id === $supplier->id) {
                    // $alreadyExists = true;
                    // break;
                    // }
                    // }

                    // if (!$alreadyExists) {
                    // $product_purchase[9][] = $supplier;
                    // }

            }
            return $product_purchase;
        }
        catch (Exception $e) {
            /*return response()->json('errors' => [$e->getMessage());*/
            //return response()->json(['errors' => [$e->getMessage()]], 422);
            return 'Something is wrong!';
        }

    }

    // public function edit($id)
    // {
    //     $role = Role::find(Auth::user()->role_id);
    //     if($role->hasPermissionTo('quotes-edit')){
    //         $lims_customer_list = Customer::where('is_active', true)->get();
    //         $lims_warehouse_list = Warehouse::where('is_active', true)->get();
    //         $lims_biller_list = Biller::where('is_active', true)->get();
    //         $lims_supplier_list = Supplier::where('is_active', true)->get();
    //         $lims_tax_list = Tax::where('is_active', true)->get();
    //         $lims_quotation_data = Quotation::find($id);
    //         $lims_product_quotation_data = ProductQuotation::where('quotation_id', $id)->get();
    //         return view('backend.quotation.edit',compact('lims_customer_list', 'lims_warehouse_list', 'lims_biller_list', 'lims_tax_list', 'lims_quotation_data','lims_product_quotation_data', 'lims_supplier_list'));
    //     }
    //     else
    //         return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
    // }

    public function edit($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('quotes-edit')){
             $lims_supplier_list = Supplier::where('is_active', true)->get();
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_production_list = Wproduction::where('is_active', true)->get();
            $lims_tax_list = Tax::where('is_active', true)->get();
            $lims_customer_list = Customer::with('user')->get();
             $lims_biller_list = Biller::where('is_active', true)->get();
            $product_purchase_log = array();
            // $product_purchase_log = product_purchase_log::with('user','customer')->where("purchase_id",$id)->get();

            if(Auth::user()->role_id > 2) 
            {
                $lims_customer_list = Customer::with('user')
                ->where('user_id', Auth::user()->id)
                ->whereHas('user', function ($query) {
                $query->where('role_id', Auth::user()->role_id);
                })
                ->get();
            }

            $lims_product_list_without_variant = $this->productWithoutVariant_();
            $lims_product_list_with_variant = $this->productWithVariant_();
            $lims_purchase_data = Quotation::find($id);
            $lims_product_purchase_data = ProductQuotation::where('quotation_id', $id)->get();

            if($lims_purchase_data->exchange_rate)
                $currency_exchange_rate = $lims_purchase_data->exchange_rate;
            else
                $currency_exchange_rate = 1;
            $custom_fields = CustomField::where('belongs_to', 'purchase')->get();
            return view('backend.quotation.edit', compact('lims_warehouse_list','lims_production_list', 'lims_supplier_list','lims_customer_list', 'lims_product_list_without_variant', 'lims_product_list_with_variant', 'lims_tax_list', 'lims_purchase_data', 'lims_product_purchase_data', 'currency_exchange_rate', 'custom_fields','product_purchase_log','lims_biller_list'));
        // echo "<pre>";
        // print_r($product_purchase_log);
        // echo "</pre>";
        // return 1;
        }
        else
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
    }



public function generatePdf($supplierId, $quotationId)
{
    $supplier = Supplier::findOrFail($supplierId);
    $purchase = Quotation::with(['supplier', 'warehouse'])->findOrFail($quotationId);
    $customer = Customer::find($purchase->user_id);
    $currency = Currency::find($purchase->currency_id);

    $products = ProductQuotation::with('unit')
        ->where('quotation_id', $quotationId)
        ->where('supplier_id', $supplierId)
        ->join('products', 'product_quotation.product_id', '=', 'products.id')
        ->select('products.name', 'products.code', 'product_quotation.*')
        ->get();

    // Calculate totals manually
    $total_shipping = 0;
    $total_tax = 0;
    $total_discount = 0;
    $sub_total = 0;

    foreach ($products as $product) {
        $row_total = ($product->net_unit_price * $product->qty) + $product->tax; // adjust as needed
        $sub_total += $row_total;
        $total_tax += $product->tax;
        $total_discount += $product->discount;
        $total_shipping += $product->ship_cost; // from product_purchases table
    }

    $grand_total = $sub_total + $total_shipping + $total_tax - $total_discount;
    $paid = $purchase->paid_amount;
    $due = $grand_total - $paid;

    $data = [
        'po_no' => $purchase->po_no,
        'system_po_no' => $purchase->system_po_no,
        'date' => $purchase->created_at->format('d/m/Y'),
        'reference_no' => $purchase->reference_no,
        'purchase_status' => $this->getQuotationStatusText($purchase->status),
        'currency_code' => $currency->code ?? 'N/A',

        'customer' => [
            'name' => $customer->name ?? '',
            'email' => $customer->email ?? '',
            'phone' => $customer->phone_number ?? '',
            'company' => $customer->company_name ?? '',
            'address' => $customer->address ?? '',
        ],

        'warehouse' => [
            'name' => $purchase->warehouse->name ?? '',
            'phone' => $purchase->warehouse->phone ?? '',
            'email' => $purchase->warehouse->email ?? '',
            'company' => $purchase->warehouse->company ?? '',
            'address' => $purchase->warehouse->address ?? '',
        ],

        'supplier' => [
            'name' => $supplier->name ?? '',
            'company' => $supplier->company_name ?? '',
            'phone' => $supplier->phone_number ?? '',
            'email' => $supplier->email ?? '',
            'address' => $supplier->address ?? '',
        ],

        'products' => $products,

        'totals' => [
            'sub_total' => $sub_total,
            'order_tax' => $total_tax,
            'order_discount' => $total_discount,
            'shipping_cost' => $total_shipping,
            'grand_total' => $grand_total,
            'paid_amount' => $paid,
            'due' => $due,
        ],

        'signature' => $purchase->signature ?? '',
        'comments' => $purchase->comments ?? '',
        'ship_instruction' => $purchase->ship_instruction ?? '',
    ];

    $pdf = \PDF::loadView('pdf.quotation', $data)->setPaper('a4');
    return $pdf->stream("single_quotation-{$supplierId}.pdf") ;
    
}


private function getQuotationStatusText($status)
{
    switch ($status) {
        case 1: return 'Pending';
        case 2: return 'sent';
       
        default: return 'Pending';
    }
}

    public function update(Request $request, $id)
    {  
        $data = $request->except('document');
       //return dd($data);
        $document = $request->document;
        $lims_quotation_data = Quotation::find($id);

        if($document) {
            $v = Validator::make(
                [
                    'extension' => strtolower($request->document->getClientOriginalExtension()),
                ],
                [
                    'extension' => 'in:jpg,jpeg,png,gif,pdf,csv,docx,xlsx,txt',
                ]
            );
            if ($v->fails())
                return redirect()->back()->withErrors($v->errors());

            $this->fileDelete(public_path('documents/quotation/'), $lims_quotation_data->document);

            $ext = pathinfo($document->getClientOriginalName(), PATHINFO_EXTENSION);
            $documentName = date("Ymdhis");
            if(!config('database.connections.saleprosaas_landlord')) {
                $documentName = $documentName . '.' . $ext;
                $document->move(public_path('documents/quotation'), $documentName);
            }
            else {
                $documentName = $this->getTenantId() . '_' . $documentName . '.' . $ext;
                $document->move(public_path('documents/quotation'), $documentName);
            }
            $data['document'] = $documentName;
        }
        $lims_product_quotation_data = ProductQuotation::where('quotation_id', $id)->get();
        //update quotation table
        $data['quotation_status'] = $data['status'];
        $lims_quotation_data->update($data);
        if($lims_quotation_data->quotation_status == 2){
            //collecting mail data
            $lims_customer_data = Customer::find($data['customer_id']);
            $mail_data['email'] = $lims_customer_data->email;
            $mail_data['reference_no'] = $lims_quotation_data->reference_no;
            $mail_data['total_qty'] = $data['total_qty'];
            $mail_data['total_price'] = $data['total_price'];
            $mail_data['order_tax'] = $data['order_tax'];
            $mail_data['order_tax_rate'] = $data['order_tax_rate'];
            $mail_data['order_discount'] = $data['order_discount'];
            $mail_data['shipping_cost'] = $data['shipping_cost'];
            $mail_data['grand_total'] = $data['grand_total'];
        }
        $product_id = $data['product_id'];
        // $product_batch_id = $data['product_batch_id'];
        // $product_variant_id = $data['product_variant_id'];
        $qty = $data['qty'];
        $sale_unit = $data['sale_unit'];
        $net_unit_price = $data['net_unit_price'];
        $discount = $data['discount'];
        $tax_rate = $data['tax_rate'];
        $tax = $data['tax'];
        $total = $data['subtotal'];
        $ets_date = $data['ets_date'];
        $eta_date = $data['eta_date'];
        $lt_date = $data['lt_date'];
        $moq = $data['moq'];
        $ship_cost = $data['ship_cost'];
        $supplier_id = $data['supplier_name'];

        foreach ($lims_product_quotation_data as $key => $product_quotation_data) {
            $old_product_id[] = $product_quotation_data->product_id;
            $lims_product_data = Product::select('id')->find($product_quotation_data->product_id);
        //     if($product_quotation_data->variant_id) {
        //         $lims_product_variant_data = ProductVariant::select('id')->FindExactProduct($product_quotation_data->product_id, $product_quotation_data->variant_id)->first();
        //         $old_product_variant_id[] = $lims_product_variant_data->id;
        //         if(!in_array($lims_product_variant_data->id, $product_variant_id))
        //             $product_quotation_data->delete();
        //     }
        //     else {
        //         $old_product_variant_id[] = null;
        //         if(!in_array($product_quotation_data->product_id, $product_id))
        //             $product_quotation_data->delete();
        //     }
        }
       
        foreach ($product_id as $i => $pro_id) {
            if($sale_unit[$i] != 'n/a'){
                $lims_sale_unit_data = Unit::where('unit_name', $sale_unit[$i])->first();
                $sale_unit_id = $lims_sale_unit_data->id;
            }
            else
                $sale_unit_id = 0;
            $lims_product_data = Product::select('id', 'name', 'is_variant')->find($pro_id);
            if($sale_unit_id)
                $mail_data['unit'][$i] = $lims_sale_unit_data->unit_code;
            else
                $mail_data['unit'][$i] = '';
            $input['quotation_id'] = $id;
            $input['product_id'] = $pro_id;
            // $input['product_batch_id'] = $product_batch_id[$i];
            $input['qty'] = $mail_data['qty'][$i] = $qty[$i];
            $input['sale_unit_id'] = $sale_unit_id;
            $input['net_unit_price'] = $net_unit_price[$i];
            $input['discount'] = $discount[$i];
            $input['tax_rate'] = $tax_rate[$i];
            $input['tax'] = $tax[$i];
            $input['total'] = $mail_data['total'][$i] = $total[$i];
            $input['eta_date'] = date('Y-m-d', strtotime($eta_date[$i]));
            $input['ets_date'] = date('Y-m-d', strtotime($ets_date[$i]));
            $input['lt_date'] = $lt_date[$i];
            $input['moq'] = $moq[$i];
            $input['ship_cost'] = $ship_cost[$i];
            $input['supplier_id'] = $supplier_id[$i];
            $flag = 1;
            if($lims_product_data->is_variant) {
                $lims_product_variant_data = ProductVariant::select('variant_id')->where('id', $product_variant_id[$i])->first();
                $input['variant_id'] = $lims_product_variant_data->variant_id;
                if(in_array($product_variant_id[$i], $old_product_variant_id)) {
                    ProductQuotation::where([
                        ['product_id', $pro_id],
                        ['variant_id', $input['variant_id']],
                        ['quotation_id', $id]
                    ])->update($input);
                }
                else {
                    ProductQuotation::create($input);
                }
                $variant_data = Variant::find($input['variant_id']);
                $mail_data['products'][$i] = $lims_product_data->name . ' [' . $variant_data->name . ']';
            }
            else {
                $input['variant_id'] = null;
                if(in_array($pro_id, $old_product_id)) {
                    ProductQuotation::where([
                        ['product_id', $pro_id],
                        ['quotation_id', $id]
                    ])->update($input);
                }
                else {
                    ProductQuotation::create($input);
                }
                $mail_data['products'][$i] = $lims_product_data->name;
            }
        }

        $message = 'Quotation updated successfully';
        $mail_setting = MailSetting::latest()->first();
        if($lims_quotation_data->quotation_status == 2 && $mail_data['email'] && $mail_setting) {
            $this->setMailInfo($mail_setting);
            try{
                Mail::to($mail_data['email'])->send(new QuotationDetails($mail_data));
            }
            catch(\Exception $e){
                $message = 'Quotation updated successfully. Please setup your <a href="setting/mail_setting">mail setting</a> to send mail.';
            }
        }
        return redirect('quotations')->with('message', $message);
    }

    public function createSale($id)
    {
        $lims_customer_list = Customer::where('is_active', true)->get();
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_biller_list = Biller::where('is_active', true)->get();
        $lims_tax_list = Tax::where('is_active', true)->get();
        $lims_quotation_data = Quotation::find($id);
        $lims_product_quotation_data = ProductQuotation::where('quotation_id', $id)->get();
        $lims_pos_setting_data = PosSetting::latest()->first();
        return view('backend.quotation.create_sale',compact('lims_customer_list', 'lims_warehouse_list', 'lims_biller_list', 'lims_tax_list', 'lims_quotation_data','lims_product_quotation_data', 'lims_pos_setting_data'));
    }

    public function createPurchase_old($id)
    {
        $lims_supplier_list = Supplier::where('is_active', true)->get();
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_tax_list = Tax::where('is_active', true)->get();
        $lims_quotation_data = Quotation::find($id);
        $lims_product_quotation_data = ProductQuotation::where('quotation_id', $id)->get();
        $lims_product_list_without_variant = $this->productWithoutVariant();
        $lims_product_list_with_variant = $this->productWithVariant();

        return view('backend.quotation.create_purchase_old',compact('lims_product_list_without_variant', 'lims_product_list_with_variant', 'lims_supplier_list', 'lims_warehouse_list', 'lims_tax_list', 'lims_quotation_data','lims_product_quotation_data'));
    }

     public function createPurchase($id)
    {
         $role = Role::find(Auth::user()->role_id);
        $lims_supplier_list = Supplier::where('is_active', true)->get();
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_production_list = Wproduction::where('is_active', true)->get();
            $lims_tax_list = Tax::where('is_active', true)->get();
            $lims_customer_list = Customer::with('user')->get();
           
            if(Auth::user()->role_id > 2) 
            {
                $lims_customer_list = Customer::with('user')
                ->where('user_id', Auth::user()->id)
                ->whereHas('user', function ($query) {
                $query->where('role_id', Auth::user()->role_id);
                })
                ->get();
            }

            $lims_product_list_without_variant = $this->productWithoutVariant();
            $lims_product_list_with_variant = $this->productWithVariant();
            $lims_purchase_data = Quotation::find($id);
            $lims_product_purchase_data = ProductQuotation::where('quotation_id', $id)->get();
            if($lims_purchase_data->exchange_rate)
                $currency_exchange_rate = $lims_purchase_data->exchange_rate;
            else
                $currency_exchange_rate = 1;
            $custom_fields = CustomField::where('belongs_to', 'purchase')->get();
            return view('backend.quotation.create_purchase', compact('lims_warehouse_list','lims_production_list', 'lims_supplier_list','lims_customer_list', 'lims_product_list_without_variant', 'lims_product_list_with_variant', 'lims_tax_list', 'lims_purchase_data', 'lims_product_purchase_data', 'currency_exchange_rate', 'custom_fields'));
       
       
    }

    public function productWithoutVariant()
    {
        return Product::ActiveStandard()->select('id', 'name', 'code')
                ->whereNull('is_variant')->get();
    }

    public function productWithVariant()
    {
        return Product::join('product_variants', 'products.id', 'product_variants.product_id')
                ->ActiveStandard()
                ->whereNotNull('is_variant')
                ->select('products.id', 'products.name', 'product_variants.item_code')
                ->orderBy('position')->get();
    }




    public function productWithoutVariant_()
{
    return Product::ActiveStandard()
        ->whereNull('is_variant')
        ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
        ->select(
            'products.id',
            'products.name',
            'products.code',
            'products.price',
            'brands.title as title'
        )
        ->get();
}

  public function productWithVariant_()
{
    return Product::join('product_variants', 'products.id', '=', 'product_variants.product_id')
        ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
        ->ActiveStandard()
        ->whereNotNull('is_variant')
        ->select(
            'products.id',
            'products.name',
            'product_variants.item_code',
            'product_variants.qty',
            'products.price',
            'brands.title as brand_title'
        )
        ->orderBy('position')
        ->get();
}


    public function deleteBySelection(Request $request)
    {
        $quotation_id = $request['quotationIdArray'];
        foreach ($quotation_id as $id) {
            $lims_quotation_data = Quotation::find($id);
            $lims_product_quotation_data = ProductQuotation::where('quotation_id', $id)->get();
            foreach ($lims_product_quotation_data as $product_quotation_data) {
                $product_quotation_data->delete();
            }
            $lims_quotation_data->delete();
            $this->fileDelete(public_path('documents/quotation/'), $lims_quotation_data->document);
        }
        return 'Quotation deleted successfully!';
    }

    public function destroy($id)
    {
        $lims_quotation_data = Quotation::find($id);
        $lims_product_quotation_data = ProductQuotation::where('quotation_id', $id)->get();
        foreach ($lims_product_quotation_data as $product_quotation_data) {
            $product_quotation_data->delete();
        }
        $lims_quotation_data->delete();
        $this->fileDelete(public_path('documents/quotation/'), $lims_quotation_data->document);
        return redirect('quotations')->with('not_permitted', __('db.Quotation deleted successfully'));
    }
}
