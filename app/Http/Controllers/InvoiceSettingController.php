<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\InvoiceSchema;
use App\Models\InvoiceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Spatie\Permission\Models\Role;

class InvoiceSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $role = Role::firstOrCreate(['id' => Auth::user()->role_id]);
        if (!$role->hasPermissionTo('invoice_setting'))
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        
        $data['invoiceSettings'] = InvoiceSetting::all();
        return view('backend.setting.invoice_setting.index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $role = Role::firstOrCreate(['id' => Auth::user()->role_id]);
        if (!$role->hasPermissionTo('invoice_create_edit_delete'))
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));

        return view('backend.setting.invoice_setting.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if(!env('USER_VERIFIED')) {
            return redirect()->back()->with('not_permitted', __('db.This feature is disable for demo!'));
        }

        $role = Role::firstOrCreate(['id' => Auth::user()->role_id]);
        if (!$role->hasPermissionTo('invoice_create_edit_delete'))
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        

        $request->validate(['template_name' => 'required|string|max:255']);
        try{
            DB::beginTransaction();
            $data = $this->getRequestData($request);
            InvoiceSetting::query()->create($data);
            DB::commit();
            return redirect()->route('settings.invoice.index')->with('customMessage', 'Invoice setting stored successfully.');
        }catch(\Throwable $e){
            DB::rollBack();
            return redirect()->route('settings.invoice.index')->with('customMessage','Failed to store invoice setting');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $role = Role::firstOrCreate(['id' => Auth::user()->role_id]);
        if (!$role->hasPermissionTo('invoice_create_edit_delete'))
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));

        $invoice = InvoiceSetting::findOrFail($id);
        return view('backend.setting.invoice_setting.edit',compact('invoice'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if(!env('USER_VERIFIED')) {
            return redirect()->back()->with('not_permitted', __('db.This feature is disable for demo!'));
        }

        if($request->ajax()){
            $data = $this->changeStatus($request,$id);
            return true;
        }
        $request->validate([
            'template_name' => 'required|string|max:255',
            'prefix' => 'string|max:10',
        ]);
        try{
            DB::beginTransaction();

            $data = $this->getRequestData($request);
            InvoiceSetting::query()->findOrFail($id)->update($data);
            DB::commit();
            return redirect()->back()->with('customMessage', 'Invoice setting stored successfully');
        }catch(\Throwable $e){
            DB::rollBack();

            return redirect()->back()->with(['customMessage' => 'Failed to store invoice setting'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if(!env('USER_VERIFIED')) {
            return response()->json(['not_permitted' => __('db.This feature is disable for demo!')]);
        }

        $role = Role::firstOrCreate(['id' => Auth::user()->role_id]);
        if (!$role->hasPermissionTo('invoice_create_edit_delete'))
            return response()->json(['not_permitted' => __('db.Sorry! You are not allowed to access this module')]);
        
        $invoice = InvoiceSetting::findOrFail($id);

        if ($invoice->is_default != 1) {
            $invoice->delete();
            return response()->json(['message' => 'Invoice deleted successfully', 'success' => true]);
        } else {
            return response()->json(['message' => 'Default invoice cannot be deleted', 'success' => false]);
        }
    }


    public function getRequestData($request){
            $data = $request->all();
            if($request->hasFile('company_logo')){
                $data['company_logo'] = $this->uploadInvoiceTemplate($request->company_logo);
            }
            if($request->hasFile('preview_invoice')){
                $data['preview_invoice'] = $this->uploadInvoiceTemplate($request->preview_invoice);
            }


            if($request->status == 1){
                InvoiceSetting::query()->where('status', 1)->update(['status' => 0]);
            }

            if($request->is_default == 1){
                InvoiceSetting::query()->where('is_default', 1)->update(['is_default' => 0]);
            }

            //checkbox data
            // Default 0 for all checkboxes unless checked
            $checkboxFields = [
                'show_barcode',
                'show_qr_code',
                'show_description',
                'show_in_words',
                'active_primary_color',
                'show_warehouse_info',
                'show_bill_to_info',
                'show_footer_text',
                'show_biller_info',
                'show_payment_note',
                'show_ref_number',
                'active_date_format',
                'active_generat_settings',
                'active_logo_height_width'
            ];

            $showColumn = [];

            foreach ($checkboxFields as $field) {
                $showColumn[$field] = isset($request->show_column[$field]) ? 1 : 0;
            }
            // Store JSON data
            $data['show_column'] = json_encode($showColumn);
            $data['status'] = isset($request->status) ? 1 : 0;
            $data['is_default'] = isset($request->is_default) ? 1 : 0;
            return $data;
    }


    private function uploadInvoiceTemplate($request_image)
    {
        if ($request_image) {
            $file = $request_image;
            $ext = $file->getClientOriginalExtension();
            $fileName = 'invoice_' . Str::random(10) . '.' . $ext;

            // Define path in the public directory
            $destinationPath = public_path('invoices');

            // Create directory if it doesn't exist
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            // Check if the file is an image
            if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png'])) {
                // Resize and save original image
                $image = (new ImageManager())->make($file->getRealPath());
                $image->resize($request->logo_width ?? 250, $request->logo_height ?? 150)->save($destinationPath . '/' . $fileName);
            } else {
                // Just move non-image files
                $file->move($destinationPath, $fileName);
            }

            // Return public URL
            return asset('invoices/' . $fileName);
        }

        return null;
    }


    public function changeStatus($request,$id){
        if($request->column == 'status'){
            InvoiceSetting::query()->where('status', 1)->update(['status' => 0]);
            InvoiceSetting::query()->findOrFail($id)->update(['status' => 1]);
            return true;
        }

        if($request->column == 'is_default'){
            InvoiceSetting::query()->where('is_default', 1)->update(['is_default' => 0]);
            InvoiceSetting::query()->findOrFail($id)->update(['is_default' => 1]);
            return true;
        }
    }
}
