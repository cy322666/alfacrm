<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;
use App\Services\amoCRM\Client as amoApi;
use App\Services\AlfaCRM\Client as alfaApi;
use Illuminate\Support\Facades\Log;

class amoController extends Controller
{
    public function __construct(
        private amoApi $amoApi,
        private alfaApi $alfaApi,
    ) {}

    //записан на пробное
    public function recorded(Request $request)
    {
        $request = $request->toArray()['status'][0] ?? $request->toArray()['add'][0];

        try {

            $lead = $this->amoApi
                ->service
                ->leads()
                ->find($request['id']);

            //TODO запрос доп полей

            $contact = $lead->contact;

            $model = Lead::query()->create([
                'amo_contact_id'    => $contact->id ?? null,
                'amo_contact_phone' => $contact->cf('Телефон')->getValue(),
                'amo_contact_email' => $contact->cf('Email')->getValue(),
//                'amo_children_name',
//                'amo_children_bd',
//                'amo_lead_source',
//                'amo_lead_instagram',
//                'amo_lead_vk',
//                'amo_lead_notes',
                'amo_lead_id'   => $lead->id,
                'amo_lead_name' => $lead->name,
                'amo_contact_name' => $contact->name ?? null,
            ]);

        } catch (\Exception $exception) {

            dd($exception->getMessage());

            Log::info(__METHOD__.' : '.$exception->getMessage());
        }




//        $leadId = $request['id'];
//        $statusId = $request['status_id'];
//        $pipelineId = $request['pipeline_id'];

        //Log::info(__METHOD__, [ $leadId, $statusId, $pipelineId ]);


    }
}
