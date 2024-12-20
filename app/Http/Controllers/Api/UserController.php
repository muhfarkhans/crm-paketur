<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $roleId = Auth::user()->role_id;
        $companyId = Auth::user()->company->company_id ?? 0;
        $isNotAdmin = $roleId != 1 ? true : false;

        $perPage = $request->get('per_page') ?? 2;
        $q = $request->get('search');
        $sort = explode(":", $request->get('sort'));
        $filterRole = $request->get('role');

        $employes = User::
            when($isNotAdmin, function ($query) use ($roleId) {
                if ($roleId == 2) {
                    $query->where('role_id', '!=', 1);
                } else {
                    $query->where('role_id', 3);
                }
            })
            ->when($isNotAdmin, function ($query) use ($companyId) {
                $query->whereHas('company', function ($query) use ($companyId) {
                    $query->where('company_id', $companyId);
                });
            })
            ->when($filterRole, function ($query) use ($filterRole, $roleId) {
                if ($roleId != 1 && in_array($filterRole, [2, 3])) {
                    $query->where('role_id', $filterRole);
                }
            })
            ->when($q, function ($query) use ($q) {
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
            "data" => $employes,
        ]);
    }

    public function find($id)
    {
        $roleId = Auth::user()->role_id;
        $companyId = Auth::user()->company->company_id ?? 0;
        $isNotAdmin = $roleId != 1 ? true : false;

        $user = User::where('id', $id)
            ->when($isNotAdmin, function ($query) use ($companyId) {
                $query->whereHas('company', function ($query) use ($companyId) {
                    $query->where('company_id', $companyId);
                });
            })->first();

        if ($user == null) {
            return response()->json([
                "status" => false,
                "errors" => 'Data not found',
            ], 404);
        }

        if (in_array($roleId, [2, 3])) {
            if ($user && Auth::user()->role_id == 3 && $user->role_id != $roleId) {
                return response()->json([
                    "status" => false,
                    "errors" => 'Unauthorized',
                ], 403);
            }
        }

        return response()->json([
            "status" => true,
            "data" => $user,
        ]);
    }

    public function create(Request $request)
    {
        $rules = [
            'name' => 'required',
            'company_id' => 'required',
            'role_id' => 'required',
            'email' => 'required|unique:users',
            'phone' => 'required|unique:users',
            'address' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(["status" => false, "errors" => $validator->errors()], 422);
        }

        if (in_array($request->input('role_id'), [1, 2])) {
            if (Auth::user()->role_id != 1) {
                return response()->json([
                    "status" => false,
                    "errors" => 'Unauthorized',
                ], 403);
            }
        }

        if (Auth::user()->role_id != 1 && Auth::user()->company->company_id != $request->input('company_id')) {
            return response()->json([
                "status" => false,
                "errors" => 'Unauthorized',
            ], 403);
        }

        $company = Company::where('id', $request->input('company_id'))->first();
        if ($company == null)
            return response()->json(["status" => false, "errors" => "data not found"], 404);

        $role = Role::where('id', $request->input('role_id'))->first();
        if ($role == null)
            return response()->json(["status" => false, "errors" => "data not found"], 404);

        $newUser = [
            'name' => $request->input("name"),
            'email' => $request->input("email"),
            'phone' => $request->input("phone"),
            'address' => $request->input("address"),
            'password' => bcrypt($request->input("phone")),
            'role_id' => $request->input("role_id"),
        ];

        $created = User::create($newUser);
        CompanyUser::create([
            'user_id' => $created->id,
            'company_id' => $company->id,
        ]);

        return response()->json([
            "status" => true,
            "data" => [
                "user" => $created,
            ]
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::where('id', $id)->first();
        if ($user == null)
            return response()->json(["status" => false, "errors" => "data not found"], 404);

        $rules = [
            'name' => 'required',
            'company_id' => 'nullable',
            'role_id' => 'required',
            'email' => 'required|unique:users,email,' . $id,
            'phone' => 'required|unique:users,phone,' . $id,
            'address' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(["status" => false, "errors" => $validator->errors()], 422);
        }

        $roleId = $request->input('role_id');
        $companyId = $request->input('company_id');

        if ($roleId == 1 && Auth::user()->role_id != 1) {
            return response()->json([
                "status" => false,
                "errors" => 'Unauthorized',
            ], 403);
        }

        if ($user->role_id == 1 && Auth::user()->role_id != 1) {
            return response()->json([
                "status" => false,
                "errors" => 'Unauthorized',
            ], 403);
        }

        if (Auth::user()->role_id != 1 && Auth::user()->company->company_id != $user->company->company_id) {
            return response()->json([
                "status" => false,
                "errors" => 'Unauthorized',
            ], 403);
        }

        if ($companyId) {
            $company = Company::where('id', $companyId)->first();
            if ($company == null)
                return response()->json(["status" => false, "errors" => "data company not found"], 404);
        }

        $role = Role::where('id', $request->input('role_id'))->first();
        if ($role == null)
            return response()->json(["status" => false, "errors" => "data not found"], 404);

        $newUser = [
            'name' => $request->input("name"),
            'email' => $request->input("email"),
            'phone' => $request->input("phone"),
            'address' => $request->input("address"),
            'role_id' => $request->input("role_id"),
        ];

        $updated = User::where('id', $id)->update($newUser);

        if ($companyId && $user->company->company_id != $companyId) {
            CompanyUser::where('user_id', $id)->delete();
            CompanyUser::create([
                "user_id" => $id,
                "company_id" => $request->input("company_id")
            ]);
        }

        return response()->json([
            "status" => true,
            "data" => [
                "update" => $updated,
            ]
        ]);
    }

    public function delete($id)
    {
        $user = User::where('id', $id)->first();
        if ($user == null)
            return response()->json(["status" => false, "errors" => "data not found"], 404);

        if (Auth::user()->role_id != 1 && Auth::user()->company->company_id != $user->company->company_id) {
            return response()->json([
                "status" => false,
                "errors" => 'Unauthorized',
            ], 403);
        }

        $deleted = User::where('id', $id)->delete();
        CompanyUser::where('user_id', $id)->delete();

        return response()->json([
            "status" => true,
            "data" => [
                "delete" => $deleted,
            ]
        ]);
    }
}
