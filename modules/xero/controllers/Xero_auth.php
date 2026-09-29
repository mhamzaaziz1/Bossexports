<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Xero_auth extends AdminController {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * QB Oauth Endpoint start
	 *
	 * @throws Exception
	 */
	public function index() {
		// Note: this controller is likely to be accessed through a popup window,
		// so I tried to keep it simple. Once the authorization is completed, you'll
		// have the access token in session, hence, you can redirect to another controller
		// where you want to do some magic :)

		$clientSecret = get_xero_clientSecret();
		$clientId = get_xero_clientID();
		$resource_owner_details = 'https://api.xero.com/api.xro/2.0/Organisation';

		$loProvider = new \League\OAuth2\Client\Provider\GenericProvider([
			'clientId' => $clientId,
			'clientSecret' => $clientSecret,
			'redirectUri' => site_url('xero/xero_auth'),
			'urlAuthorize' => 'https://login.xero.com/identity/connect/authorize',
			'urlAccessToken' => 'https://identity.xero.com/connect/token',
			'urlResourceOwnerDetails' => $resource_owner_details,
			'scopes' => ['openid email profile offline_access assets projects accounting.settings accounting.transactions accounting.contacts accounting.journals.read accounting.reports.read accounting.attachments'],
			'state' => app_generate_hash(),
			'response_type' => 'code',
		]);

		// If we don't have an authorization code then get one
		if (!$this->input->get('code', true)) {

			// Fetch the authorization URL from the provider; this returns the
			// urlAuthorize option and generates and applies any necessary parameters
			// (e.g. state).
			$lsAuthorizationUrl = $loProvider->getAuthorizationUrl();

			// Get the state generated for you and store it to the session.
			$this->session->set_userdata('oauth2state', $loProvider->getState());

			// Redirect the user to the authorization URL.
			header('Location: ' . $lsAuthorizationUrl);
			exit;
		} else {
			// Check given state against previously stored one to mitigate CSRF attack
			if (strcmp($this->session->userdata('oauth2state'), $this->input->get('state', true)) != 0) {
				log_message('error', "The state is not correct from Xero Server. Consider your app as hacked.");
			}

			// Try to get an access token using the authorization code grant.
			try {
				$loAccessToken = $loProvider->getAccessToken('authorization_code', ['code' => $this->input->get('code', true)]);

				//Tenant ID

				$jwt = new XeroAPI\XeroPHP\JWTClaims();
				$jwt->setTokenId($loAccessToken->getValues()["id_token"]);
				$jwt->decode();

				$config = XeroAPI\XeroPHP\Configuration::getDefaultConfiguration()->setAccessToken((string) $loAccessToken->getToken());
				$identityInstance = new XeroAPI\XeroPHP\Api\IdentityApi(
					new GuzzleHttp\Client(),
					$config
				);

				// Get Array of Tenant Ids
				$result = $identityInstance->getConnections();

				// Storing access token and some other important data in the db.
				// Pay attention to each value, as we will them later and it can get
				// confuse if you don't know where they came from later.
				//
				// For more elaborated approaches you might want to save these into
				// the DB and then have a Cron job validating the token periodically
				// (E.g. using the expiration date, etc.)

				update_option('xero_access_token', $loAccessToken->getToken());
				update_option('xero_refresh_token', $loAccessToken->getRefreshToken());
				update_option('xero_expiration_date', $loAccessToken->getExpires());
				update_option('xero_is_expired', $loAccessToken->hasExpired());
				update_option('xero_realmid', $this->input->get('realmId', true));
				update_option('xero_refresh_token_expires_in', time() + 60 * 24 * 60 * 60); //60 days
				update_option('xero_tenant_id', $result[0]->getTenantId());
				update_option('xero_connection_id', $result[0]->getId());
				update_option('xero_tenant_name', $result[0]->getTenantName());

			} catch (League\OAuth2\Client\Provider\Exception\IdentityProviderException $ex) {
				log_message('error', $ex->getMessage());
				exit;
			}

		}

		// Closing popup and refreshing parent window
		print '<script type="text/javascript">
    	window.opener.location.href = window.opener.location.href;
    	window.close();
    	</script>';
	}

	public function disconnect() {
		if ($this->input->is_ajax_request()) {
			$this->load->model('xero_model');
			$result = $this->xero_model->disconnect();
			if ($result) {
				update_option('xero_access_token', '');
				update_option('xero_refresh_token', '');
				update_option('xero_expiration_date', '');
				update_option('xero_is_expired', '');
				update_option('xero_realmid', '');
				update_option('xero_refresh_token_expires_in', '');
				update_option('xero_tenant_id', '');
				update_option('xero_connection_id', '');
				update_option('xero_tenant_name', '');

				echo json_encode(array(
					'success' => true,
					'message' => _l('xero_disconnection_success'),
				));
			} else {
				echo json_encode(array(
					'success' => false,
					'message' => _l('xero_disconnection_fail'),
				));
			}
			die;
		}

		return true;
	}
}