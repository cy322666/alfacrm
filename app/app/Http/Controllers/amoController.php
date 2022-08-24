<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Services\AlfaCRM\Client;
use App\Services\AlfaCRM\Helpers\Leads;
use App\Services\amoCRM\Helpers\Contacts;
use App\Services\amoCRM\Helpers\Notes;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\amoCRM\Client as amoApi;
use App\Services\AlfaCRM\Client as alfaApi;
use Illuminate\Support\Facades\Log;
use Nikitanp\AlfacrmApiPhp\Entities\Customer;

class amoController extends Controller
{
    private \Nikitanp\AlfacrmApiPhp\Client $alfaApi;

    public function __construct(
        private amoApi $amoApi,
        Request $request,
    ) {
//        Log::info(__METHOD__. ' > '.$request->method(), $request->toArray());

        $this->alfaApi = alfaApi::init();
    }

    //записан на пробное
    public function recorded(Request $request)
    {
        try {
            $request = $request->toArray()['leads']['status'][0] ?? $request->toArray()['leads']['add'][0];

            $lead = $this->amoApi
                ->service
                ->leads()
                ->find($request['id']);

            $contact = $lead->contact;

            $branch = $lead->cf('Адрес клуба')->getValue();
            $source = $lead->cf('Откуда узнали')->getValue();

            $branchId = match ($branch) {
                'Октябрьской революции, 62' => Client::BRANCH_4_ID,
                'Полтавская, 39'  => Client::BRANCH_3_ID,
                'Коминтерна, 182' => Client::BRANCH_2_ID,
            };

            $sourceId = match ($source) {
                'Реклама в ВК'         => Client::SOURCE_4_ID,
                'Реклама в интернете'  => Client::SOURCE_8_ID,
                'Поиск в интернете'    => Client::SOURCE_9_ID,
                'Рекомендация знакомых'=> Client::SOURCE_2_ID,
                'Живут рядом'          => Client::SOURCE_10_ID,
                'ДубльГис'             => Client::SOURCE_11_ID,
                'Реклама в парке им. 1 Мая' => Client::SOURCE_12_ID,
            };

            $model = Lead::query()
                ->create([
                    'amo_contact_id'      => $contact->id ?? null,
                    'amo_contact_phone'   => Contacts::clearPhone($contact->cf('Телефон')->getValue()),
                    'amo_contact_email'   => $contact->cf('Email')->getValue(),
                    'alfa_branch_id'      => $branchId,
                    'amo_children_1_name' => $contact->cf('ФИО ребенка 1')->getValue(),
                    'amo_children_2_name' => $contact->cf('ФИО ребенка 2')->getValue(),
                    'amo_children_1_bd'   => $contact->cf('День рождения ребенка 1')->getValue(),
                    'amo_children_2_bd'   => $contact->cf('День рождения ребенка 2')->getValue(),
                    'amo_lead_instagram'  => $contact->cf('Instagram')->getValue(),
                    'amo_lead_source'     => $lead->cf('Откуда узнали')->getValue(),
                    //                'amo_lead_vk',
                    'amo_lead_notes'   => $contact->cf('Примечание')->getValue(),
                    'amo_lead_id'      => $lead->id,
                    'amo_contact_name' => $contact->name ?? null,
                ]);

            $customers = (new Customer($this->alfaApi))
                ->get(0, [
                    'removed' => 1,
                    'phone'   => $model->amo_contact_phone,
                ]);

            if ($customers['total'] > 0) {

                $customerId = $customers['items'][0]['id'];
                $branchIds  = $customers['items'][0]['branch_ids'];

            } else {

                $response = (new Customer($this->alfaApi))
                    ->create([
                        'name' => $model->amo_contact_name,
                        'branch_ids' => [$model->alfa_branch_id],
                        'is_study'   => Client::CLIENT_STUDY,
                    ]);

                if ($response['success'] == true) {

                    $customerId = $response['model']['id'];
                } else {

                    dd($response['errors']);
                }
            }

            $result = (new Customer($this->alfaApi))
                ->update($customerId, [
                    'lead_source_id'  => $sourceId,
                    //                    'assigned_id' => ,
                    'legal_type' => Client::CLIENT_TYPE_ID,
                    'legal_name' => $model->amo_contact_name,
                    'dob' => Carbon::parse($model->amo_children_1_bd)->format('d.m.Y'),
                    //                    'balance' => ,//TODO
                    //                    'paid_lesson_count' => ,
                    'phone' => $model->amo_contact_phone,
                    'email' => $model->amo_contact_email,
//                    'custom_source' => $model->amo_lead_source,
                    'web'  => "https://podvodoinn.amocrm.ru/contacts/detail/{$contact->id}",
                    'note' => $model->amo_lead_notes,
                    'name' => $model->amo_contact_name,
                    'branch_ids' => !empty($branchIds) ? array_merge([$model->alfa_branch_id], $branchIds) : [$model->alfa_branch_id],
                    'is_study'   => Client::CLIENT_STUDY,

                    'custom_fiovtorogorebenka'    => $model->amo_children_2_name,
                    'custom_datarozhdeniyvtorogo' => Carbon::parse($model->amo_children_2_bd)->format('d.m.Y'),
                ]);

            if ($result['success'] !== true) {

                dd($result['errors']);
            }

            $model->alfa_client_id = $customerId ?? null;
            $model->status = 1;
            $model->save();

            Notes::addOne($lead, 'Успешно отправлен в AlfaCRM');

            $contact->cf('Ссылка в AlfaCRM')->setValue(
                "https://podvodoinn.s20.online/company/{$model->alfa_branch_id}/customer/view?id={$model->alfa_client_id}"
            );
            $contact->save();

            dd($result);

        } catch (\Exception $exception) {

            dd($exception->getMessage());
        }
    }
}
