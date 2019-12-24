<?php

namespace App\Http\Controllers\Api;

use App\Common\Utils;
use App\Http\Controllers\Controller;
use AppleSignIn\ASDecoder;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Ecdsa\Sha256;
use Lcobucci\JWT\Signer\Key;

class AuthorizationController extends Controller {

    // JWT 验证
    public function jwtApple(Request $request) {

        // 授权的用户唯一标识
        $user = $request->input('user');
        // 邮箱
        $email = $request->input('email');
        // 用户信息
        $fullName = $request->input('fullName');
        // 授权code 并没有用到
        $authorizationCode = $request->input('authorizationCode');

        // 授权用户的JWT凭证
        $identityToken = $request->input('identityToken');

        $appleSignInPayload = ASDecoder::getAppleSignInPayload($identityToken);

        $isValid = $appleSignInPayload->verifyUser($user);

        // 当 $isValid 为 true 时验证通过，后续逻辑根据需求编写
        dd($isValid);
    }

    // 授权码 验证
    public function authApple(Request $request) {

        // 授权的用户唯一标识
        $user = $request->input('user');
        // 邮箱
        $email = $request->input('email');
        // 用户信息
        $fullName = $request->input('fullName');
        // 授权code
        $authorizationCode = $request->input('authorizationCode');
        // 授权用户的JWT凭证
        $identityToken = 'eyJraWQiOiJBSURPUEsxIiwiYWxnIjoiUlMyNTYifQ.eyJpc3MiOiJodHRwczovL2FwcGxlaWQuYXBwbGUuY29tIiwiYXVkIjoiY29tLndhbmdxdWFud2VpLlNpZ25JbldpdGhBcHBsZSIsImV4cCI6MTU3NzE5NzUwMywiaWF0IjoxNTc3MTk2OTAzLCJzdWIiOiIwMDE3OTkuODUzNmMzZDk5MmFmNGNmYThjNzg2NzAzODQyMTA2MGQuMTAwOCIsImNfaGFzaCI6IlhZZU1EQmhCbW82YVBkWVVSLU91cWciLCJhdXRoX3RpbWUiOjE1NzcxOTY5MDN9.KlfEtY2SUJiFrDTsrZg061GkyArn2ESBg2HGdWgsQGlXzKGC4p8LU8kIFKKEqttRXgmhUbi0vgzLf9SD3DRmj2FQWGCPxh1p2vnUOpxPs8PuOQiDApYRUu76n3XdaFB8LuTS3iIE68ZUrjWzeD6642c75AfYzXoJGpo1UlU1Q0IFxWZB-aYCNqfY3Yjr1GR4WeTsMBV6oDUj6L6jG2Wf7LZpkPVFUfM7fIVWAcjG1HYZ_f5sP2wqPrlcL_mnwvcX2pPorc3kxBOcbTamfJyKEHsv6jREKWQjRF-b4UiFqlSrIpGIMuobBvdsjXlxKtp1Q0E3nnwWGPZNNyoZ38CxHw';

        // 解析 identityToken
        $tks = explode('.', $identityToken);

        // 当前时间
        $now = time();

        $signer = new Sha256();
        $privateKey = new Key('file://'.resource_path().'/AuthKey_2KJ47JV979.p8');

        $token = (new Builder())->issuedBy('1234567890')    // iss
            ->permittedFor('https://appleid.apple.com') // 写死
            ->withHeader('kid', '1234567890') // apple 开发者后台-keys-Key ID
            ->withHeader('alg', 'ES256')    // 写死
            ->issuedAt($now)
            ->expiresAt($now + 86400 * 30)
            ->withClaim('sub', json_decode(Utils::urlsafeB64Decode($tks[1]))->aud)
            ->getToken($signer, $privateKey)
            ->__toString();

        dd($token);
        
    }
}
