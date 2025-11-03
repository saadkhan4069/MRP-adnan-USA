<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wproduction;
use Illuminate\Validation\Rule;
use Keygen;
use Auth;
use DB;
use App\Traits\CacheForget;

class wproductionController extends Controller
{  
    use CacheForget;
    public function index()
    {
        $lims_warehouse_all = Wproduction::where('is_active', true)->get();
        $numberOfWarehouse = Wproduction::where('is_active', true)->count();
        return view('backend.production.create', compact('lims_warehouse_all', 'numberOfWarehouse'));
    }

   public function store(Request $request)
{
    $this->validate($request, [
        'name' => [
            'required',
            'max:255',
            Rule::unique('wproductions')->where(function ($query) {
                return $query->where('is_active', 1);
            }),
        ],
    ]);

    // Sirf necessary fields nikal lo
    $input = $request->all();
    $input['is_active'] = true;

    Wproduction::create($input);

    return redirect('production')->with('message', __('db.Data inserted successfully'));
}

    public function edit($id)
    {
        $lims_warehouse_data = Wproduction::findOrFail($id);
        return $lims_warehouse_data;
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => [
                'max:255',
                    Rule::unique('wproductions')->ignore($request->warehouse_id)->where(function ($query) {
                    return $query->where('is_active', 1);
                }),
            ],
        ]);
        $input = $request->all();
        $lims_warehouse_data = Wproduction::find($input['warehouse_id']);
        $lims_warehouse_data->update($input);
      
        return redirect('production')->with('message', __('db.Data updated successfully'));
    }

    public function importproduction(Request $request)
    {
        //get file
        $upload=$request->file('file');
        $ext = pathinfo($upload->getClientOriginalName(), PATHINFO_EXTENSION);
        if($ext != 'csv')
            return redirect()->back()->with('not_permitted', __('db.Please upload a CSV file'));
        $filename =  $upload->getClientOriginalName();
        $upload=$request->file('file');
        $filePath=$upload->getRealPath();
        //open and read
        $file=fopen($filePath, 'r');
        $header= fgetcsv($file);
        $escapedHeader=[];
        //validate
        foreach ($header as $key => $value) {
            $lheader=strtolower($value);
            $escapedItem=preg_replace('/[^a-z]/', '', $lheader);
            array_push($escapedHeader, $escapedItem);
        }
        //looping through othe columns
        while($columns=fgetcsv($file))
        {
            if($columns[0]=="")
                continue;
            foreach ($columns as $key => $value) {
                $value=preg_replace('/\D/','',$value);
            }
           $data= array_combine($escapedHeader, $columns);

           $warehouse = Wproduction::firstOrNew([ 'name'=>$data['name'], 'is_active'=>true ]);
           $warehouse->name = $data['name'];
           $warehouse->phone = $data['phone'];
           $warehouse->email = $data['email'];
           $warehouse->address = $data['address'];
           $warehouse->is_active = true;
           $warehouse->save();
        }
        return redirect('production')->with('message', __('db.Warehouse imported successfully'));
    }

    public function deleteBySelection(Request $request)
    {
        $warehouse_id = $request['warehouseIdArray'];
        foreach ($warehouse_id as $id) {
            $lims_warehouse_data = Wproduction::find($id);
            $lims_warehouse_data->is_active = false;
            $lims_warehouse_data->save();
        }
        return 'Warehouse deleted successfully!';
    }

    public function destroy($id)
    {
        $lims_warehouse_data = Wproduction::find($id);
        $lims_warehouse_data->is_active = false;
        $lims_warehouse_data->save();
        return redirect('production')->with('not_permitted', __('db.Data deleted successfully'));
    }

    public function productionAll()
    {
        if(Auth::user()->role_id > 2)
            $lims_warehouse_list = DB::table('wproductions')->where([
            ['is_active', true],
            ['id', Auth::user()->warehouse_id]
        ])->get();
        else
            $lims_warehouse_list = DB::table('warehouses')->where('is_active', true)->get();

        $html = '';
        foreach($lims_warehouse_list as $warehouse){
            $html .='<option value="'.$warehouse->id.'">'.$warehouse->name.'</option>';
        }

        return response()->json($html);
    }
}
