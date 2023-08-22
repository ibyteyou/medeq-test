<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

function errorHandler ($errorText) {
    return response($errorText, 403)->header('Content-Type', 'text/html');
}

function checkLength ($string, $length) {
    return mb_strlen($string) > $length;
}

Route::post('document-generator', function (Request $request) {
    $billNumber = 1;
    $currentDate = date('d.m.Y');

    $data = $request->all();
    $logo = $data['logo'];
    $providerName = $data['providerName'];
    if (!$providerName) {
        return errorHandler('Наименование поставщика не заполнено');
    }
    if (checkLength($providerName, 255)) {
        return errorHandler('Наименование поставщика должно быть не более 255 символов');
    }
    $providerInn = $data['providerInn'];
    if (!$providerInn) {
        return errorHandler('ИНН поставщика не заполнено');
    }
    if (checkLength($providerInn, 30)) {
        return errorHandler('ИНН поставщика должно быть не более 30 символов');
    }
    $providerKpp = $data['providerKpp'];
    if (!$providerKpp) {
        return errorHandler('КПП поставщика не заполнено');
    }
    if (checkLength($providerKpp, 30)) {
        return errorHandler('КПП поставщика должно быть не более 30 символов');
    }
    $providerAddress = $data['providerAddress'];
    $consumerName = $data['consumerName'];
    if (!$consumerName) {
        return errorHandler('ФИО покупателя не заполнено');
    }
    if (checkLength($consumerName, 255)) {
        return errorHandler('ФИО покупателя должно быть не более 255 символов');
    }
    $consumerInn = $data['consumerInn'];
    if (!$consumerInn) {
        return errorHandler('ИНН покупателя не заполнено');
    }
    if (checkLength($consumerInn, 30)) {
        return errorHandler('ИНН покупателя должно быть не более 30 символов');
    }
    $consumerAddress = $data['consumerAddress'];
    $goods = $data['goods'];
    if (!count($goods)) {
        return errorHandler('Список товаров не заполнен');
    }


    $pdf = App::make('dompdf.wrapper');
    $pdf->set_paper('А4', 'landscape');
    $html = '<!doctype html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Счет</title>
        <style>body { font-family: DejaVu Sans }</style>
        <style>
            .main {
                width: 978px;
                margin: 0 auto;
                font-size: 17px;
            }
        </style>
    </head>
    <body>
    <div class="main">';
    $html .= '<table width="100%">
    <tr >
        <td style="width: 68%; padding: 20px 0;">
            <div style="text-align: justify; font-size: 11pt;">Внимание! Оплата данного счета означает согласие с условиями поставки товара. Счет действителен в течение 5 (пяти) банковских дней, не считая дня выписки счета.</div>
        </td>
        <td style="width: 32%; text-align: center;">';
    if ($logo) {
        $html .= '<img src="' . $logo . '" style="width: 200px; height: auto;">';
    }
    $html .= '</td>
    </tr>

</table>';
    $html .= '<table width="100%" border="2" style="border-collapse: collapse; width: 100%;" cellpadding="2" cellspacing="2">
    <tr style="">
        <td colspan="2" rowspan="2" style="min-height:13mm; width: 105mm;">
            <table width="100%" border="0" cellpadding="0" cellspacing="0" style="height: 13mm;">
                <tr>
                    <td valign="bottom" style="height: 3mm;">
                        <div style="font-size:10pt;">Банк получателя</div>
                    </td>
                </tr>
                <tr>
                    <td valign="top">
                        <div>МОСКОВСКИЙ БАНК ВЫСОКИХ ТЕХНОЛОГИЙ</div>
                    </td>
                </tr>
            </table>
        </td>
        <td style="min-height:7mm;height:auto; width: 25mm;">
            <div>БИK</div>
        </td>
        <td rowspan="2" style="vertical-align: top; width: 60mm;">
            <div style=" height: 7mm; line-height: 7mm; vertical-align: middle;">0440000000</div>
            <div>404300320203402042304234</div>
        </td>
    </tr>
    <tr>
        <td style="width: 25mm;">
            <div>к/c</div>
        </td>
    </tr>
    <tr>
        <td colspan="2" style="min-height:13mm; height:auto;">

            <table border="0" cellpadding="0" cellspacing="0" style="height: 13mm; width: 105mm;">
                <tr>
                    <td valign="top">
                        <div>ООО "' . $providerName . '"</div>
                    </td>
                </tr>
            </table>

        </td>
        <td rowspan="2" style="min-height:19mm; height:auto; vertical-align: top; width: 25mm;">
            <div>р/c</div>
        </td>
        <td style="min-height:19mm; height:auto; vertical-align: top; width: 60mm;">
            <div>23329423043024320422</div>
        </td>
    </tr>
</table>';
    $html .= ' <br/>

    <div style="font-weight: bold; font-size: 25pt; padding-left:5px;">
        Счет № ' . $billNumber . ' от ' . $currentDate . '</div>
    <br/>

    <div style="background-color:#000000; width:100%; font-size:1px; height:2px;">&nbsp;</div>';
    $html .= '<table width="100%">
    <tr>
        <td style="width: 30mm; vertical-align: top;">
            <div style=" padding-left:2px; ">Поставщик:</div>
        </td>
        <td>
            <div style="font-weight:bold;  padding-left:2px;">
                ООО "' . $providerName . '" ИНН ' . $providerInn . ', КПП ' . $providerKpp . '<br>
                <span style="font-weight: normal;">
                    ' . $providerAddress . '
                </span>
            </div>
        </td>
    </tr>
    <tr>
        <td style="width: 30mm; vertical-align: top;">
            <div style=" padding-left:2px;">Покупатель:</div>
        </td>
        <td>
            <div style="font-weight:bold;  padding-left:2px;">
                ИП ' . $consumerName . ', ИНН ' . $consumerInn . '<br><span style="font-weight: normal;">' . $consumerAddress . '</span>
            </div>
        </td>
    </tr>
</table>';
    $html .= '<table border="2" width="100%" cellpadding="2" cellspacing="2" style="border-collapse: collapse; width: 100%;">
    <thead>
    <tr>
        <th style="width:13mm; ">№</th>
        <th>Товары (работы, услуги)</th>
        <th style="width:20mm;">Кол-во</th>
        <th style="width:17mm;">Ед.</th>
        <th style="width:27mm;">Цена</th>
        <th style="width:27mm;">Сумма</th>
    </tr>
    </thead>
    <tbody >';
    $total = 0;
    $totalSum = 0;
    for ($i = 0; $i < count($goods); $i++) {
        if (!$goods[$i]['name']) {
            return errorHandler('Наименование товара в строке ' . $i + 1 . ' не заполнено');
        }
        if (checkLength($goods[$i]['name'], 255)) {
            return errorHandler('Наименование товара в строке ' . $i + 1 . ' должно быть не более 255 символов');
        }
        $html .= '<tr>
            <td style="width:13mm;">
                ' . $i + 1 . '
            </td>
            <td>
                ' . $goods[$i]['name'] . '
            </td>
            <td style="width:20mm; white-space: nowrap;">
                ' . $goods[$i]['count'] . '
            </td>
            <td style="width:17mm;  white-space: nowrap;">
                ' . $goods[$i]['qty'] . '
            </td>
            <td style="width:27mm; text-align: center;  white-space: nowrap; ">
                ' . $goods[$i]['price'] . ' руб.
            </td>
            <td style="width:27mm; text-align: center;  white-space: nowrap;">
                ' . $goods[$i]['count'] * $goods[$i]['price'] . ' руб.
            </td>
        </tr>';
        $total += $goods[$i]['count'];
        $totalSum += $goods[$i]['count'] * $goods[$i]['price'];
    }
    $html .= '</tbody>
</table>';
    $html .= '<table border="0" width="100%" cellpadding="1" cellspacing="1">
    <tr>
        <td></td>
        <td style="width:37mm; font-weight:bold;  text-align:right;">Всего к оплате:</td>
        <td style="width:27mm; font-weight:bold;  text-align: center; ">' . $totalSum . ' руб.</td>
    </tr>
</table>

<br />
<div>
    Всего наименований ' . $total . ' на сумму ' . $totalSum . ' руб.<br />
</div>';
    $html .= '
    <hr style="margin-bottom: 25px; margin-top: 25px;">
    <div>
        <div>Руководитель ______________________ </div>
        <br/>  <br /><br />
        <div>Главный бухгалтер ______________________</div>
        <br/>
        <div style="width: 85mm;text-align:center;">М.П.</div><br/>
    </div>
    </div>
    </body>
    </html>';
    $pdf->loadHTML($html);
    return $pdf->stream();
});
