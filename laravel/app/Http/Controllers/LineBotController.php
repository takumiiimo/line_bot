<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;

use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;

use LINE\LINEBot\Event\MessageEvent\TextMessage;

class LineBotController extends Controller
{
    public function index()
    {
        return view('linebot.index');
    }
    
    public function parrot(Request $request)
    {
        Log::debug($request->header());
        Log::debug($request->input());

        $httpClient = new CurlHTTPClient(env('LINE_ACCESS_TOKEN'));
        $lineBot = new LINEBot($httpClient,['channelSecret' => env('LINE_CHANNEL_SECRET')]);

        // ログ出力時にも登場した$request->header()は、引数を渡すと、それをキーとする値を返す
        $signature = $request->header('x-line-signature');
        // validateSignatureメソッドは、メッセージボディと署名を引数として受け取り、署名の検証を行う
        if(!$lineBot->validateSignature($request->getContent(), $signature)) {
            // false だったら　リクエストが不正であるとする
            abort(400, 'Invalid signature');
        }
        // メッセージの種類に応じたクラスのインスタンスを返します。例）テキストメッセージであればLINE\LINEBot\Event\MessageEvent\TextMessageクラス
         $events = $lineBot->parseEventRequest($request->getContent(), $signature);

         Log::debug($events);

         foreach($events as $event){
             if(!($event instanceof TextMessage)){
                 Log::debug('Non text message has come');
                 continue;
             }
             $replyToken = $event->getReplyToken();
             
             $replyText = $event->getText();
            //  LINEBotクラスのreplyTextメソッドで、テキストメッセージでの返信が行われる。第一引数には応答トークンを、第二引数には返信内容のテキストを渡す
             $lineBot->replyText($replyToken, $replyText);
         }
    }
}