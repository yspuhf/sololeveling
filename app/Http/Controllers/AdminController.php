<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    public function users()
    {
        return view('admin.users.index');
    }

    public function userDetail($id)
    {
        return view('admin.users.show', ['userId' => $id]);
    }

    public function payments()
    {
        return view('admin.payments');
    }

    public function subscriptions()
    {
        return view('admin.subscriptions');
    }

    public function contracts()
    {
        return view('admin.contracts');
    }

    public function features()
    {
        return view('admin.features');
    }

    public function plans()
    {
        return view('admin.plans');
    }

    public function auditLogs()
    {
        return view('admin.audit-logs');
    }
}
