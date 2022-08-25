<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Services\AlfaCRM\Client as alfaApi;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Client as amoApi;
use App\Services\amoCRM\Helpers\Contacts;
use App\Services\amoCRM\Helpers\Leads;
use App\Services\amoCRM\Helpers\Notes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Nikitanp\AlfacrmApiPhp\Entities\Customer;
use Nikitanp\AlfacrmApiPhp\Entities\CustomerTariff;
use Nikitanp\AlfacrmApiPhp\Entities\Tariff;

class AlfaController extends Controller
{
    public function __construct(
        private amoApi $amoApi,
        Request $request)
    {
        Log::info(__METHOD__. ' > '.$request->method(), $request->toArray());

        $this->alfaApi = alfaApi::init();
    }

    //посетил пробное
    public function came(Request $request)
    {
        $model = Lead::query()
            ->where('alfa_client_id', $request->fields_new->customer_id)
            ->firstOrFail();

        if ($model->status == 1) {

            //проверка подходит ли под условия?
            $lead = $this->amoApi
                ->service
                ->leads()
                ->find($model->amo_lead_id);

            $lead->status_id = Client::STATUS_ID_CAME;
            $lead->save();
        }

        Notes::addOne($lead, 'Клиент пришел на пробное, карточка обновлена');

        $model->status = 2;
        $model->save();
    }

    //пропустил пробное
    public function omission(Request $request)
    {
        $model = Lead::query()
            ->where('alfa_client_id', $request->fields_new->customer_id)
            ->firstOrFail();

        if ($model->status == 1) {

            //проверка подходит ли под условия?
            $lead = $this->amoApi
                ->service
                ->leads()
                ->find($model->amo_lead_id);

            $lead->status_id = Client::STATUS_ID_OMISSION;
            $lead->save();
        }

        Notes::addOne($lead, 'Клиент пропустил пробное');

        $model->status = 3;
        $model->save();
    }

    //клиент ушел в архив
    public function archive(Request $request)
    {
        $model = Lead::query()
            ->where('alfa_client_id', $request->entity_id)
            ->first();

        if (!$model) {

            $customer = (new Customer($this->alfaApi))->getFirst(['id' => $request->entity_id]);

            if ($customer) {

                $contact = Contacts::search([
                    'Телефоны' => $customer['phone'],
                    'Почта'    => $customer['email'][0],
                ], $this->amoApi->service);

                if ($contact) {

                    $leads = $contact->leads->toArray();

                    foreach ($leads as $lead) {

                        if ($lead['pipeline_id'] == Client::TARIFF_PIPELINE_ID &&
                            $lead['status_id'] !== 142 && $lead['status_id'] !== 143) {

                            $lead = $this->amoApi->service->leads()->find($lead['id']);


                        }
                    }

                } else {

                    //TODO нет такого в амо? кек
                }
            }
        }
    }

    //получение оплаты
    public function pay(Request $request)
    {
        $model = Lead::query()
            ->where('alfa_client_id', $request->fields_new['customer_id'])
            ->firstOrFail();

        if ($model->status == 1) {

            //проверка подходит ли под условия?
            $lead = $this->amoApi
                ->service->leads()
                ->find($model->amo_lead_id);

            $lead->status_id = 142;
            $lead->save();

            Notes::addOne($lead, 'Клиент совершил оплату на сумму '.$request->fields_new['income']);

            $model->status = 4;
            $model->save();

            $tariffs = (new CustomerTariff($this->alfaApi))->get(0, [
                'customer_id' => $model->alfa_client_id,
            ]);

            if ($tariffs['total'] == 0) {

                Notes::addOne($lead, 'От клиента получена оплата, но у него нет абонементов');

                $model->status = 40;
                $model->save();

            } else {

                $tariff = (new Tariff($this->alfaApi))->getFirst([
                    'id' => $tariffs['items'][0]['tariff_id'],
                ]);

                $lead->sale =  explode('.', $tariff['price'])[0];
                $lead->save();

                Notes::addOne($lead, 'Карточка обновлена информацией абонемента');
            }

            $tasks = $lead->tasks->toArray();

            foreach ($tasks as $task) {

                if ($task['is_completed'] == false) {

                    $task['is_completed'] = true;

                    $taskDetail = $this->amoApi->service->tasks()->find($task['id']);
                    $taskDetail->save();

                    unset($taskDetail);

                    Notes::addOne($lead, 'Задачи в сделке завершены');
                }
            }
        }
    }

    //закончился абонемент
    //TODO возможно проверка при добавлении/посещении урока по хуку не пробного
    public function ended(Request $request)
    {

    }

    //повторная покупка абонемента
    public function repeated(Request $request)
    {
        $customer = (new Customer($this->alfaApi))->getFirst(['id' => $request->entity_id]);

        if ($customer) {

            $contact = Contacts::search([
                'Телефоны' => $customer['phone'],
                'Почта'    => $customer['email'][0],
            ], $this->amoApi->service);

            if ($contact) {

                $leads = $contact->leads->toArray();

                if (count($leads) > 0) {

                    foreach ($leads as $lead) {

                        if ($lead['pipeline_id'] == Client::TARIFF_PIPELINE_ID &&
                            $lead['status_id'] !== 142 && $lead['status_id'] !== 143) {

                            $lead = $this->amoApi->service->leads()->find($lead['id']);

                            $lead->status_id = 142;
                            $lead->save();

                            break;
                        }
                        unset($lead);
                    }
                }

                if (empty($lead)) {

                    $lead = $contact->createLead([
                        'name' => '',
                        'sale' => '',
                    ]);
                }

            } else {

                //TODO нет такого в амо? кек
            }
        }

            Notes::addOne($lead, 'Клиент совершил оплату на сумму '.$request->fields_new['income']);

        //TODO создать если нет
//            $model->status = 8;
//            $model->save();

//            $tariffs = (new CustomerTariff($this->alfaApi))->get(0, [
//                'customer_id' => $model->alfa_client_id,
//            ]);
//
//            if ($tariffs['total'] == 0) {
//
//                Notes::addOne($lead, 'От клиента получена оплата, но у него нет абонементов');
//
//                $model->status = 80;
//                $model->save();
//
//            } else {
//
//                $tariff = (new Tariff($this->alfaApi))->getFirst([
//                    'id' => $tariffs['items'][0]['tariff_id'],
//                ]);
//
//                $lead->sale =  explode('.', $tariff['price'])[0];
//                $lead->save();
//
//                Notes::addOne($lead, 'Карточка обновлена информацией абонемента');
//            }

            $tasks = $lead->tasks->toArray();

            foreach ($tasks as $task) {

                if ($task['is_completed'] == false) {

                    $task['is_completed'] = true;

                    $taskDetail = $this->amoApi->service->tasks()->find($task['id']);
                    $taskDetail->save();

                    unset($taskDetail);

                    Notes::addOne($lead, 'Задачи в сделке завершены');
                }
            }
        }
//    }
}
