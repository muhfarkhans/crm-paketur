<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page') ?? 2;
        $q = $request->get('search');
        $sort = explode(":", $request->get('sort'));

        $companies = Company::
            when($q, function ($query) use ($q) {
                $query->where('name', 'like', '%' . $q . '%');
            })
            ->when($sort, function ($query) use ($sort) {
                $direction = count($sort) > 1 ? $sort[1] : "DESC";
                $column = $sort[0] != "" ? $sort[0] : "id";
                $query->orderBy($column, $direction);
            })
            ->simplePaginate($perPage)
            ->withQueryString();

        return response()->json([
            "status" => true,
            "data" => $companies,
        ]);
    }

    public function find($id)
    {
        $company = Company::where('id', $id)->first();
        return response()->json([
            "status" => true,
            "data" => $company,
        ]);
    }


    public function create(Request $request)
    {
        $rules = [
            'name' => 'required|unique:companies',
            'email' => 'required|unique:companies',
            'phone' => 'required|unique:companies',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(["status" => false, "errors" => $validator->errors()], 422);
        }

        $newCompany = [
            'name' => $request->input("name"),
            'email' => $request->input("email"),
            'phone' => $request->input("phone"),
        ];

        $created = Company::create($newCompany);
        $createdUser = User::create([
            'name' => 'admin',
            'email' => 'admin@' . explode("@", $newCompany["email"])[1],
            'password' => bcrypt($newCompany["phone"]),
            'role_id' => 2,
            'phone' => $newCompany["phone"],
            'address' => "-",
        ]);

        CompanyUser::create([
            'user_id' => $createdUser->id,
            'company_id' => $created->id,
        ]);

        return response()->json([
            "status" => true,
            "data" => [
                "company" => $created,
            ]
        ]);
    }

    public function update(Request $request, $id)
    {
        $company = Company::where('id', $id)->first();
        if ($company == null)
            return response()->json(["status" => false, "errors" => "data not found"], 404);

        $rules = [
            'name' => 'required|unique:companies,name,' . $company->id,
            'email' => 'required|unique:companies,email,' . $company->id,
            'phone' => 'required|unique:companies,phone,' . $company->id,
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(["status" => false, "errors" => $validator->errors()], 422);
        }

        $newCompany = [
            'name' => $request->input("name"),
            'email' => $request->input("email"),
            'phone' => $request->input("phone"),
        ];

        $updated = Company::where('id', $id)->update($newCompany);

        return response()->json([
            "status" => true,
            "data" => [
                "update" => $updated,
            ]
        ]);
    }

    public function delete($id)
    {
        $deleted = Company::where('id', $id)->delete();
        User::whereIn('id', function ($query) use ($id) {
            $query->select('user_id')
                ->from('company_users')
                ->where('company_id', $id);
        })->delete();
        CompanyUser::where('company_id', $id)->delete();

        return response()->json([
            "status" => true,
            "data" => [
                "delete" => $deleted,
            ]
        ]);
    }
}
