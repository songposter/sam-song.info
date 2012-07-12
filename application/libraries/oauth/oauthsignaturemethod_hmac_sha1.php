<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * The HMAC-SHA1 signature method uses the HMAC-SHA1 signature algorithm as defined in [RFC2104]
 * where the Signature Base String is the text and the key is the concatenated values (each first
 * encoded per Parameter Encoding) of the Consumer Secret and Token Secret, separated by an '&'
 * character (ASCII code 38) even if empty.
 *   - Chapter 9.2 ("HMAC-SHA1")
 */
class OAuthSignatureMethod_HMAC_SHA1 {
	function get_name() {
		return "HMAC-SHA1";
	}

	public function build_signature($request, $consumer, $token) {
		$base_string = $request->get_signature_base_string();
		$request->base_string = $base_string;

		$key_parts = array(
				$consumer->secret,
				($token) ? $token->secret : ""
		);

		$key_parts = OAuthUtil::urlencode_rfc3986($key_parts);
		$key = implode('&', $key_parts);

		return base64_encode(hash_hmac('sha1', $base_string, $key, true));
	}

	/**
	 * Verifies that a given signature is correct
	 * @param OAuthRequest $request
	 * @param OAuthConsumer $consumer
	 * @param OAuthToken $token
	 * @param string $signature
	 * @return bool
	 */
	public function check_signature($request, $consumer, $token, $signature) {
		$built = $this->build_signature($request, $consumer, $token);

		// Check for zero length, although unlikely here
		if (strlen($built) == 0 || strlen($signature) == 0) {
			return false;
		}

		if (strlen($built) != strlen($signature)) {
			return false;
		}

		// Avoid a timing leak with a (hopefully) time insensitive compare
		$result = 0;
		for ($i = 0; $i < strlen($signature); $i++) {
			$result |= ord($built{$i}) ^ ord($signature{$i});
		}

		return $result == 0;
	}
}

/* End of File: oauthsignaturemethod_hmac_sha1.php */
/* Location: ./application/libraries/oauthsignaturemethod_hmac_sha1.php */