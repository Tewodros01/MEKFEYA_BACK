<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Organaization\OrganaizationModel;
use phpseclib3\File\ASN1\Maps\OrganizationName;

class OrganizationController extends Controller
{
    //
    public function index()
    {
        $organizations = OrganaizationModel::all();
        return response($organizations,200);
    }
    public function create(Request $request)
    {
        try {
            DB::beginTransaction();
            $validator =  validator($request->all(), [
                'organizationName' => 'required',
                'contactPerson' => 'required',
                'contactEmail' => 'required',
                'contactPhone' => 'required',
                'country' => 'required',
                'subCity' => 'required',
                'woreda' => 'required',
                'houseNo' => 'required',
                'addressLine' => 'required',
                'tinNo' => 'required',
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors()->getMessages();
                return response()->json($errors, 417);
            }
            $organization = new OrganaizationModel();
            $organization->organization_name = $request->organizationName;
            $organization->country =  $request->country;
            $organization->sub_city =  $request->subCity;
            $organization->woreda =  $request->woreda;
            $organization->house_no = $request->houseNo;
            $organization->address_line =  $request->addressLine;
            $organization->contact_person =   $request->contactPerson;
            $organization->contact_email = $request->contactEmail;
            $organization->contact_phone = $request->contactPhone;
            $organization->fax = $request->fax;
            $organization->po_box = $request->poBox;
            $organization->website_address = $request->websiteAddress;
            $organization->vat_no = $request->vatNo;
            $organization->vat_reg_date = $request->vatRegDate;
            $organization->tin_no = $request->tinNo;

            $organization->save();
            if ($organization) {
                $organizationId = $organization->id;
                DB::commit();
                return OrganaizationModel::find($organizationId)->first();
            } else {
                DB::rollback();
                return response()->json(['error' => 'organization is not added, request fialed '], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'organization is not added, error happened',
                'message' => $e->getMessage()
            ], 500);
        };
    }
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $organization = OrganaizationModel::find($id);

            $organization->organization_name = $request->organizationName;
            $organization->country =  $request->country;
            $organization->sub_city =  $request->subCity;
            $organization->woreda =  $request->woreda;
            $organization->house_no = $request->houseNo;
            $organization->address_line =  $request->addressLine;
            $organization->contact_person =   $request->contactPerson;
            $organization->contact_email = $request->contactEmail;
            $organization->contact_phone = $request->contactPhone;
            $organization->fax = $request->fax;
            $organization->po_box = $request->poBox;
            $organization->website_address = $request->websiteAddress;
            $organization->vat_no = $request->vatNo;
            $organization->vat_reg_date = $request->vatRegDate;
            $organization->tin_no = $request->tinNo;

            $organization->update();
            if ($organization) {
                $organizationId = $organization->id;
                DB::commit();
                return OrganaizationModel::find($organizationId)->first();
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'organization is not added, error happened',
                'message' => $e->getMessage()
            ], 500);
        };
    }

    public function show($id)
    {
        try {
            $organization = OrganaizationModel::findOrFail($id);
            return response()->json($organization, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'organization not found'], 404);
        }
    }

    public function delete($id)
    {
        try {
            if ($id) {
                $organization = OrganaizationModel::findOrFail($id);
                $organization->delete();
                return response()->json(['success' => 'item deleted successfully'], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'error deleting item from items list'], 500);
        }
    }
}
