<?php

namespace App\Http\Controllers;

use App\Models\addHooks;
use Illuminate\Http\Request;

class AddHooksController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->all();
        if (isset($data['contacts']['add'])) {
            $this->addContact($data);
        } elseif (isset($data['contacts']['update'])) {
            $this->updateContacts();
        }
    }
    public function addContact($data)
    {
        $contacts = $data['contacts']['add'][0];

        $dealId = $contacts['id'];
        $dealName = $contacts['name'];
        $responsibleUser = $contacts['responsible_user_id'];
        $timeCreated = $contacts['date_create'];

        $addHook = new addHooks();
        $addHook->dealname = $dealName;
        $addHook->responsible = $responsibleUser;
        $addHook->timecreated = $timeCreated;
        $addHook->save();
    }
    public function updateContacts()
    {
        $addHook = new addHooks();
        $addHook->dealname = "dealName";
        $addHook->responsible = "responsibleUser";
        $addHook->timecreated = "timeCreated";
        $addHook->save();
    }
}
