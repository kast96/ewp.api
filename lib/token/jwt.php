<?
namespace Ewp\Api\Token;

class JWT extends \Ewp\Api\Token\Base
{
	public static function getToken($userId)
	{
		$data = parent::getTokenData($userId);

		return self::encode($data['payload'], $data['secretKey']);
	}

	public static function checkToken()
	{
		if (!$arToken = self::decodeTokenFromHeaders())
		{
			return false;
		}

		$data = parent::getTokenData($arToken['payload']['userId']);
		$secretKey = $data['secretKey'];
		$signatureCalc = self::hashSignature($arToken['header']['alg'], base64_encode(json_encode($arToken['header'])).base64_encode(json_encode($arToken['payload'])), $secretKey);

		return $arToken['signature'] == $signatureCalc ? $arToken['payload'] : false;
	}

	public static function decodeTokenFromHeaders()
	{
		if (!$token = self::getTokenFromHeaders())
		{
			return false;
		}

		return self::decode($token);
	}

	public static function getHeader()
	{
		if (!$arToken = self::decodeTokenFromHeaders())
		{
			return false;
		}
		return $arToken['header'];
	}

	public static function getPayload()
	{
		if (!$arToken = self::decodeTokenFromHeaders())
		{
			return false;
		}

		return $arToken['payload'];
	}

	public static function getSignature()
	{
		if (!$arToken = self::decodeTokenFromHeaders())
		{
			return false;
		}
		return $arToken['signature'];
	}

	public static function hashSignature($alg, $data, $secretKey)
	{
		return hash_hmac($alg, $data, $secretKey);
	}

	public static function encode($payload, $secretKey, $alg = 'sha256')
	{
		if (!$secretKey)
		{
			return false;
		}

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