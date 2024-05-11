<?php

namespace App\Http\Controllers;

use App\Models\addHooks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AddHooksController extends Controller
{
    public $responsibleUserName;
    public $dealStatus;
    public function store(Request $request)
    {
        $data = $request->all();
        if (isset($data['contacts']['add'])) {
            $this->addContact($data);
        } elseif (isset($data['contacts']['update'])) {
            $this->updateContacts($data);
        } elseif (isset($data['leads']['add'])) {
            $this->dealAdd($data);
        } elseif (isset($data['leads']['update'])) {
            $this->updateLeads($data);
        }
    }
    public function addContact($data)
    {
        $contacts = $data['contacts']['add'][0];

        $contactId = $contacts['id'];
        $dealName = $contacts['name'];
        $responsibleUser = $contacts['responsible_user_id'];
        $timeCreated = date('Y-m-d H:i:s', strtotime($contacts['date_create']));

        // Get the username of the responsible user
        $this->user($responsibleUser);
        // Create note string
        $noteText = "$dealName was created by $this->responsibleUserName on $timeCreated. $dealName был создан $this->responsibleUserName на $timeCreated.";
        // Generate Note
        $this->noteContactAdd($noteText, $contactId);
    }
    public function updateContacts($data)
    {
        $contacts = $data['contacts']['update'][0];
        $contactId = $contacts['id'];
        $timeCreated = date('Y-m-d H:i:s', strtotime($contacts['date_create']));
        $responsibleUser = $contacts['responsible_user_id'];
        $dealName = $contacts['name'];
        $this->user($responsibleUser);


        if (isset($data['contacts']['update'][0]['custom_fields'])) {
            $customFields = $data['contacts']['update'][0]['custom_fields'];
            foreach ($customFields as $customField) {
                $fieldName = $customField['name'];
                $fieldValue = $customField['values'][0]['value'];
                $noteText = "$dealName was changed by $this->responsibleUserName on $timeCreated. Field changes is $fieldName with value $fieldValue. $dealName был изменен $this->responsibleUserName на $timeCreated. Изменено поле $fieldName со значением $fieldValue.";
            }
        } else {
            $noteText = "$dealName was changed by $this->responsibleUserName on $timeCreated. $dealName был изменен $this->responsibleUserName на $timeCreated.";
        }
        $this->noteContactAdd($noteText, $contactId);
    }
    public function user($userID)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImUyNjBkNjRiYWQ0YTBkMDE0ZjI1NDg1NzI3MDE0MWZhODVjZTExZTk3NTI3NWExNTc1N2I3M2M4YTZlNDhiYjlkNTQ4ZGFiNGM5NDZiMjk5In0.eyJhdWQiOiI2MGYwMDlkYS1iNjJjLTQ1ZTUtODY4NS1hODQxYjZlOGFhYjkiLCJqdGkiOiJlMjYwZDY0YmFkNGEwZDAxNGYyNTQ4NTcyNzAxNDFmYTg1Y2UxMWU5NzUyNzVhMTU3NTdiNzNjOGE2ZTQ4YmI5ZDU0OGRhYjRjOTQ2YjI5OSIsImlhdCI6MTcxNTI1NzYxNCwibmJmIjoxNzE1MjU3NjE0LCJleHAiOjE3NDg3MzYwMDAsInN1YiI6IjExMDE0NDIyIiwiZ3JhbnRfdHlwZSI6IiIsImFjY291bnRfaWQiOjMxNzM1NDU0LCJiYXNlX2RvbWFpbiI6ImFtb2NybS5ydSIsInZlcnNpb24iOjIsInNjb3BlcyI6WyJjcm0iLCJmaWxlcyIsImZpbGVzX2RlbGV0ZSIsIm5vdGlmaWNhdGlvbnMiLCJwdXNoX25vdGlmaWNhdGlvbnMiXSwiaGFzaF91dWlkIjoiMjFhNzFlYTAtNTJjMy00MjczLTk1MzAtYjg0NDBiZDkwYTdlIn0.WtgcJXZytE0cIj72BybUo_-Uod7hQp43KhUqOToOb_9k153_BU1DksBDWZQxMQLAApEm-5vkQ52lOEovhy03Xht3yY_0AXskvgHwC2PGD9hs7wIkR_nTkRWKhO1dKf1Kq9du5pI_bRtAzlOA9QSyD3kqjYJDWTVIrsDHlf9c69SKyf1yNT8K43LIHkOLNY4TleRpmLd5OvoIDH1ejWnVUhpbUgraaJo9BlMGwWqjnuKz8CWFLWvHL-eRRC5wGmdb9_KSoKZd4pboBXd4FUEYVGDsP5D9NEAINZBz6mUagaXoHMK8rAbYZ3Wr_saYGymv6bDkr5Qq5EWD6hve8UIssw',
            'Cookie' => 'session_id=h8cc8o4bkrebieoej7ccosd6ck; user_lang=ru'
        ])
            ->get('https://biwotlawrence.amocrm.ru/api/v4/users/' . $userID);

        $response->throw(); // Throws an exception if the request was unsuccessful.

        $data = $response->json();
        $this->responsibleUserName = $data['name'];
    }
    public function noteContactAdd($noteText, $contact_id)
    {
        $noteData = [
            [
                "note_type" => "common",
                "params" => [
                    "text" => $noteText
                ]
            ]
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImUyNjBkNjRiYWQ0YTBkMDE0ZjI1NDg1NzI3MDE0MWZhODVjZTExZTk3NTI3NWExNTc1N2I3M2M4YTZlNDhiYjlkNTQ4ZGFiNGM5NDZiMjk5In0.eyJhdWQiOiI2MGYwMDlkYS1iNjJjLTQ1ZTUtODY4NS1hODQxYjZlOGFhYjkiLCJqdGkiOiJlMjYwZDY0YmFkNGEwZDAxNGYyNTQ4NTcyNzAxNDFmYTg1Y2UxMWU5NzUyNzVhMTU3NTdiNzNjOGE2ZTQ4YmI5ZDU0OGRhYjRjOTQ2YjI5OSIsImlhdCI6MTcxNTI1NzYxNCwibmJmIjoxNzE1MjU3NjE0LCJleHAiOjE3NDg3MzYwMDAsInN1YiI6IjExMDE0NDIyIiwiZ3JhbnRfdHlwZSI6IiIsImFjY291bnRfaWQiOjMxNzM1NDU0LCJiYXNlX2RvbWFpbiI6ImFtb2NybS5ydSIsInZlcnNpb24iOjIsInNjb3BlcyI6WyJjcm0iLCJmaWxlcyIsImZpbGVzX2RlbGV0ZSIsIm5vdGlmaWNhdGlvbnMiLCJwdXNoX25vdGlmaWNhdGlvbnMiXSwiaGFzaF91dWlkIjoiMjFhNzFlYTAtNTJjMy00MjczLTk1MzAtYjg0NDBiZDkwYTdlIn0.WtgcJXZytE0cIj72BybUo_-Uod7hQp43KhUqOToOb_9k153_BU1DksBDWZQxMQLAApEm-5vkQ52lOEovhy03Xht3yY_0AXskvgHwC2PGD9hs7wIkR_nTkRWKhO1dKf1Kq9du5pI_bRtAzlOA9QSyD3kqjYJDWTVIrsDHlf9c69SKyf1yNT8K43LIHkOLNY4TleRpmLd5OvoIDH1ejWnVUhpbUgraaJo9BlMGwWqjnuKz8CWFLWvHL-eRRC5wGmdb9_KSoKZd4pboBXd4FUEYVGDsP5D9NEAINZBz6mUagaXoHMK8rAbYZ3Wr_saYGymv6bDkr5Qq5EWD6hve8UIssw',
            'Cookie' => 'session_id=h8cc8o4bkrebieoej7ccosd6ck; user_lang=ru'
        ])
            ->post('https://biwotlawrence.amocrm.ru/api/v4/contacts/' . $contact_id . '/notes', $noteData);

        $response->throw();
        // Throws an exception if the request was unsuccessful.

        echo $response->body();
    }
    public function noteDealAdd($noteText, $contact_id)
    {
        $noteData = [
            [
                "note_type" => "common",
                "params" => [
                    "text" => $noteText
                ]
            ]
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImUyNjBkNjRiYWQ0YTBkMDE0ZjI1NDg1NzI3MDE0MWZhODVjZTExZTk3NTI3NWExNTc1N2I3M2M4YTZlNDhiYjlkNTQ4ZGFiNGM5NDZiMjk5In0.eyJhdWQiOiI2MGYwMDlkYS1iNjJjLTQ1ZTUtODY4NS1hODQxYjZlOGFhYjkiLCJqdGkiOiJlMjYwZDY0YmFkNGEwZDAxNGYyNTQ4NTcyNzAxNDFmYTg1Y2UxMWU5NzUyNzVhMTU3NTdiNzNjOGE2ZTQ4YmI5ZDU0OGRhYjRjOTQ2YjI5OSIsImlhdCI6MTcxNTI1NzYxNCwibmJmIjoxNzE1MjU3NjE0LCJleHAiOjE3NDg3MzYwMDAsInN1YiI6IjExMDE0NDIyIiwiZ3JhbnRfdHlwZSI6IiIsImFjY291bnRfaWQiOjMxNzM1NDU0LCJiYXNlX2RvbWFpbiI6ImFtb2NybS5ydSIsInZlcnNpb24iOjIsInNjb3BlcyI6WyJjcm0iLCJmaWxlcyIsImZpbGVzX2RlbGV0ZSIsIm5vdGlmaWNhdGlvbnMiLCJwdXNoX25vdGlmaWNhdGlvbnMiXSwiaGFzaF91dWlkIjoiMjFhNzFlYTAtNTJjMy00MjczLTk1MzAtYjg0NDBiZDkwYTdlIn0.WtgcJXZytE0cIj72BybUo_-Uod7hQp43KhUqOToOb_9k153_BU1DksBDWZQxMQLAApEm-5vkQ52lOEovhy03Xht3yY_0AXskvgHwC2PGD9hs7wIkR_nTkRWKhO1dKf1Kq9du5pI_bRtAzlOA9QSyD3kqjYJDWTVIrsDHlf9c69SKyf1yNT8K43LIHkOLNY4TleRpmLd5OvoIDH1ejWnVUhpbUgraaJo9BlMGwWqjnuKz8CWFLWvHL-eRRC5wGmdb9_KSoKZd4pboBXd4FUEYVGDsP5D9NEAINZBz6mUagaXoHMK8rAbYZ3Wr_saYGymv6bDkr5Qq5EWD6hve8UIssw',
            'Cookie' => 'session_id=h8cc8o4bkrebieoej7ccosd6ck; user_lang=ru'
        ])
            ->post('https://biwotlawrence.amocrm.ru/api/v4/leads/' . $contact_id . '/notes', $noteData);

        $response->throw();
        // Throws an exception if the request was unsuccessful.

        echo $response->body();
    }
    public function dealAdd($deals)
    {
        $deal = $deals['leads']['add'][0];

        $contactId = $deal['id'];
        $dealName = $deal['name'];
        $responsibleUser = $deal['responsible_user_id'];
        $this->user($responsibleUser);

        $timeCreated = date('Y-m-d H:i:s', strtotime($deal['date_create']));
        $noteText = "$dealName был создан $this->responsibleUserName на $timeCreated. $dealName был создан $this->responsibleUserName на $timeCreated.";
        $this->noteDealAdd($noteText, $contactId);
    }
    public function updateLeads($data)
    {

        $contacts = $data['leads']['update'][0];
        $contactId = $contacts['id'];
        $timeCreated = date('Y-m-d H:i:s', strtotime($contacts['date_create']));
        $responsibleUser = $contacts['responsible_user_id'];
        $dealName = $contacts['name'];
        $this->user($responsibleUser);
        $status = $contacts['status_id'];
        $pipelineId = $contacts['pipeline_id'];
        $this->pipelineStatus($pipelineId, $status);
        $noteText = "$dealName was changed by $this->responsibleUserName on $timeCreated and transitioned to $this->dealStatus. $dealName был изменен $this->responsibleUserName на $timeCreated и перешел в состояние $this->dealStatus.";

        $this->noteDealAdd($noteText, $contactId);
    }
    public function pipelineStatus($pipelineId, $statusId)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImUyNjBkNjRiYWQ0YTBkMDE0ZjI1NDg1NzI3MDE0MWZhODVjZTExZTk3NTI3NWExNTc1N2I3M2M4YTZlNDhiYjlkNTQ4ZGFiNGM5NDZiMjk5In0.eyJhdWQiOiI2MGYwMDlkYS1iNjJjLTQ1ZTUtODY4NS1hODQxYjZlOGFhYjkiLCJqdGkiOiJlMjYwZDY0YmFkNGEwZDAxNGYyNTQ4NTcyNzAxNDFmYTg1Y2UxMWU5NzUyNzVhMTU3NTdiNzNjOGE2ZTQ4YmI5ZDU0OGRhYjRjOTQ2YjI5OSIsImlhdCI6MTcxNTI1NzYxNCwibmJmIjoxNzE1MjU3NjE0LCJleHAiOjE3NDg3MzYwMDAsInN1YiI6IjExMDE0NDIyIiwiZ3JhbnRfdHlwZSI6IiIsImFjY291bnRfaWQiOjMxNzM1NDU0LCJiYXNlX2RvbWFpbiI6ImFtb2NybS5ydSIsInZlcnNpb24iOjIsInNjb3BlcyI6WyJjcm0iLCJmaWxlcyIsImZpbGVzX2RlbGV0ZSIsIm5vdGlmaWNhdGlvbnMiLCJwdXNoX25vdGlmaWNhdGlvbnMiXSwiaGFzaF91dWlkIjoiMjFhNzFlYTAtNTJjMy00MjczLTk1MzAtYjg0NDBiZDkwYTdlIn0.WtgcJXZytE0cIj72BybUo_-Uod7hQp43KhUqOToOb_9k153_BU1DksBDWZQxMQLAApEm-5vkQ52lOEovhy03Xht3yY_0AXskvgHwC2PGD9hs7wIkR_nTkRWKhO1dKf1Kq9du5pI_bRtAzlOA9QSyD3kqjYJDWTVIrsDHlf9c69SKyf1yNT8K43LIHkOLNY4TleRpmLd5OvoIDH1ejWnVUhpbUgraaJo9BlMGwWqjnuKz8CWFLWvHL-eRRC5wGmdb9_KSoKZd4pboBXd4FUEYVGDsP5D9NEAINZBz6mUagaXoHMK8rAbYZ3Wr_saYGymv6bDkr5Qq5EWD6hve8UIssw',
            'Cookie' => 'session_id=h8cc8o4bkrebieoej7ccosd6ck; user_lang=ru',
        ])
            ->get("https://biwotlawrence.amocrm.ru//api/v4/leads/pipelines/$pipelineId/statuses/$statusId");

        $responseData = $response->json();
        $this->dealStatus = $responseData['name'];
    }
}
