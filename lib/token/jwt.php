<?
namespace Ewp\Api\Token;

use \Bitrix\Main\SystemException;
use \Bitrix\Main\Localization\Loc;

class JWT extends \Ewp\Api\Token\Base
{
	public static function getToken($userId)
	{
		$data = parent::getTokenData($userId);

		return self::encode($data['payload'], $data['secretKey']);
	}

	public static function checkToken()
	{
		$arToken = self::decodeTokenFromHeaders();

		$data = parent::getTokenData($arToken['payload']['userId']);
		$secretKey = $data['secretKey'];
		$signatureCalc = self::hashSignature($arToken['header']['alg'], base64_encode(json_encode($arToken['header'])).base64_encode(json_encode($arToken['payload'])), $secretKey);

		return $arToken['signature'] == $signatureCalc ? $arToken['payload'] : false;
	}

	public static function decodeTokenFromHeaders()
	{
		if (!$token = self::getTokenFromHeaders())
			throw new SystemException(Loc::getMessage("ERROR_TOKEN_NOT_FOUND"));

		return self::decode($token);
	}

	public static function getHeader()
	{
		return self::decodeTokenFromHeaders()['header'];
	}

	public static function getPayload()
	{
		return self::decodeTokenFromHeaders()['payload'];
	}

	public static function getSignature()
	{
		return self::decodeTokenFromHeaders()['signature'];
	}

	public static function hashSignature($alg, $data, $secretKey)
	{
		return hash_hmac($alg, $data, $secretKey);
	}

	public static function encode($payload, $secretKey, $alg = 'sha256')
	{
		if (!$secretKey)
			throw new SystemException(Loc::getMessage("ERROR_NOT_FOUND_SECRET_KEY"));

		$header = base64_encode(json_encode(['alg' => $alg]));
		$payload = base64_encode(json_encode($payload));
		$signature = self::hashSignature($alg, $header.$payload, $secretKey);

		return implode('.', [$header, $payload, $signature]);
	}

	public static function decode($token)
	{
		$arToken = explode('.', $token);
		$jsonHeader = base64_decode($arToken[0]);
		$jsonPayload = base64_decode($arToken[1]);
		$header = json_decode($jsonHeader, true);
		$payload = json_decode($jsonPayload, true);
		$signature = $arToken[2];

		return [
			'header' => $header,
			'payload' => $payload,
			'signature' => $signature,
		];
	}
}