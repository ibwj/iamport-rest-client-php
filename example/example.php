<?php

require_once '../vendor/autoload.php';

use Iamport\RestClient\Enum\Endpoint;
use Iamport\RestClient\Iamport;
use Iamport\RestClient\Request\AgainPayment;
use Iamport\RestClient\Request\IssueBillingKey;
use Iamport\RestClient\Request\CardInfo;
use Iamport\RestClient\Request\IssueReceipt;
use Iamport\RestClient\Request\CancelPayment;
use Iamport\RestClient\Request\OnetimePayment;
use Iamport\RestClient\Request\Schedule;
use Iamport\RestClient\Request\SubscribeSchedule;
use Iamport\RestClient\Request\SubscribeUnschedule;

$iamport = new Iamport('imp_apikey', 'ekKoeW8RyKuT0zgaZsUtXXTLQ4AhPFW3ZGseDA6bkA5lamv9OqDMnxyeB9wqOsuO9W3Mx9YSJ4dTqJ3f');

$response = $iamport->callApi('GET', Endpoint::PAYMENTS.'imp_348847450502' );

// imp_uid 로 주문정보 찾기(아임포트에서 생성된 거래고유번호)
$paymentImpUID =  $iamport->paymentImpUid('imp_348847450502');

// merchant_uid 로 주문정보 찾기(가맹점의 주문번호)
$paymentMerchantUID = $iamport->paymentMerchantUid('20181008j', 'paid', '-started');

// merchant_uid 로 주문정보 모두 찾기(가맹점의 주문번호)
$paymentsMerchantUID = $iamport->paymentsMerchantUid('20181008j', 'ready', 1, '-started');

// 주문취소 ( imp_uid )
$cancelRequest = CancelPayment::withImpUid('imps_677508061377');
// 주문취소 ( merchant_uid )
$cancelRequest2 = CancelPayment::withMerchantUid('20180802g');
$cancelRequest->merchant_uid   = '20180802g';
$cancelRequest->amount         = 1004;
$cancelRequest->tax_free       = 0;
$cancelRequest->checksum       = 0;
$cancelRequest->reason         = '취소테스트';
$cancelRequest->refund_holder  = '환불될 가상계좌 예금주';
$cancelRequest->refund_bank    = '환불될 가상계좌 은행코드';
$cancelRequest->refund_account = '환불될 가상계좌 번호';
$paymentCancel = $iamport->paymentCancel($cancelRequest);

// 발행된 현금영수증 조회
$receipt = $iamport->receipt('imps_168056340072');

// 현금영수증 발행
$issueReceiptRequest              = new IssueReceipt('imps_168056340072', '01012341234');
$issueReceiptRequest->type        = 'person';
$issueReceiptRequest->buyer_name  = '구매자 이름';
$issueReceiptRequest->buyer_email = '구매자 이메일';
$issueReceiptRequest->buyer_tel   = '구매자 전화번호';
$issueReceiptRequest->tax_free    = 0;
$issueReceipt = $iamport->issueReceipt($issueReceiptRequest);

// 비인증결제 빌링키 등록(수정)
$cardInfo                          = new CardInfo('1234-1234-1234-1234', '2023-12', '880223', '01');
$billingKeyData                    = new IssueBillingKey('customer_1234', $cardInfo);
$billingKeyData->customer_name     = '고객(카드소지자) 이름';
$billingKeyData->customer_tel      = '고객(카드소지자) 전화번호';
$billingKeyData->customer_email    = '고객(카드소지자) 이메일';
$billingKeyData->customer_addr     = '고객(카드소지자) 주소';
$billingKeyData->customer_postcode = '고객(카드소지자) 우편번호';
$addBillingKey                     = $iamport->addBillingKey($billingKeyData);

// 비인증결제 빌링키 조회
$billingKey    = $iamport->billingKey('duplicate-cuid1');

// 비인증결제 빌링키 삭제
$delBillingKey = $iamport->delBillingKey('duplicate-cuid1');

// 빌링키 발급과 결제 요청을 동시에 처리.
$cardInfo                    = new CardInfo('1234-1234-1234-1234', '2023-12', '880223', '01');
$onetimeData                 = new OnetimePayment('20180802c', 1000, $cardInfo);
$onetimeData->tax_free       = 0;
$onetimeData->customer_uid   = 'customer_12345';
$onetimeData->pg             = 'pg 사';
$onetimeData->name           = '주문명';
$onetimeData->buyer_name     = '주문자명';
$onetimeData->buyer_email    = '주문자 E-mail주소';
$onetimeData->buyer_tel      = '주문자 전화번호';
$onetimeData->buyer_addr     = '주문자 주소';
$onetimeData->buyer_postcode = '주문자 우편번호';
$onetimeData->card_quota     = '카드 할부개월 수';
$onetimeData->custom_data    = '';
$onetimeData->notice_url     = 'http://notice.example.com';

$subscribeOnetime = $iamport->subscribeOnetime($onetimeData);

// 저장된 빌링키로 재결제.
$againData                 = new AgainPayment('duplicate-cuid2', 'merchant_1411448514391', 1004, '주문명');
$againData->tax_free       = 100;
$againData->buyer_name     = '주문자명';
$againData->buyer_email    = '주문자 E-mail주소';
$againData->buyer_tel      = '주문자 전화번호';
$againData->buyer_addr     = '주문자 주소';
$againData->buyer_postcode = '주문자 우편번호';
$againData->card_quota     = '카드 할부개월 수';
$againData->custom_data    = '';
$againData->notice_url     = '';
$subscribeAgain = $iamport->subscribeAgain($againData);

// 저장된 빌링키로 정기 예약 결제.
// 정기 예약 결제 객체 생성 ( required )
$scheduleData = new SubscribeSchedule('duplicate-cuid1');

// 정기 예약 결제 정보 셋팅 ( optional )
$scheduleData->checking_amount = 0;
$scheduleData->pg = '';

// 정기 예약 결제 정보 셋팅 - 카드정보
// 1. 객체형태로 삽입 ( optional )
$cardInfo = new CardInfo('1234-1234-1234-1234', '2020-01', '000000', '00');
$scheduleData->setCardInfo($cardInfo);

// 정기 예약 결제 정보 셋팅 - 카드정보
// 2. 직접삽입 ( optional )
$scheduleData->card_number = '1234-1234-1234-1234';
$scheduleData->expiry = '2020-01';
$scheduleData->birth = '000000';
$scheduleData->pwd_2digit = '00';

// 정기 예약 결제 정보 셋팅 - schedule
// schedule 객체 생성 ( required )
$schedule1 = new Schedule('1order_'.time(), time() + 100, 1100);
$schedule2 = new Schedule('2order_'.time(), time() + 100, 1200);

// 정기 예약 결제 정보 셋팅 - schedule
// schedule 정보 셋팅 ( optional )
$schedule1->tax_free = 0;
$schedule1->name = '예약결제 1';
$schedule1->buyer_name = '예약자A';
$schedule1->buyer_email = 'buyer@iamport.kr';
$schedule1->buyer_tel = '01012341234';
$schedule1->buyer_addr = '서울 강남구 신사동';
$schedule1->buyer_postcode = '123456';
$schedule1->notice_url = '';

// 정기예약할 schedule 추가
$scheduleData->addSchedules($schedule1);
$scheduleData->addSchedules($schedule2);
$subscribeSchedule   = $iamport->subscribeSchedule($scheduleData);

// 비인증 결제요청예약 취소
$unscheduleData = new SubscribeUnschedule('duplicate-cuid1');
$unscheduleData->merchant_uid = ['1order_1568016126'];
$subscribeUnschedule = $iamport->subscribeUnschedule($unscheduleData);
