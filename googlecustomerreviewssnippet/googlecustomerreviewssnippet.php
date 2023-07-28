<?php

if (!defined('_PS_VERSION_')) {
	exit;
}

class GoogleCustomerReviewsSnippet extends Module
{
	protected $config_form = false;

	public function __construct()
	{
		$this->name = 'googlecustomerreviewssnippet';
		$this->tab = 'seo';
		$this->version = '0.1.6';
		$this->author = 'Hurt-Mix';
		$this->need_instance = 0;
		$this->ps_versions_compliancy = [
            'min' => '1.7.0.0',
            'max' => '8.99.99',
        ];
		$this->bootstrap = true;

		parent::__construct();

		$this->displayName = $this->l('Google Customer Reviews Snippet');
		$this->description = $this->l('This modules adds the Google Customer Reviews snippet in the order confirmation page.');
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
		$this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
	
	}

//instalacja
	 
	public function install()
	{
	if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
       }
       return ( parent::install()
    	 && Configuration::updateValue('GCRS_MERCHANT_ID', '')
         && Configuration::updateValue('GCRS_ENABLED_BADGE', 0)
         && Configuration::updateValue('GCRS_BADGE_POSITION', 'BOTTOM_LEFT')
         && Configuration::updateValue('GCRS_ENABLED_LOG', 0)
         && Configuration::updateValue('GCRS_OPT_IN_STYLE','CENTER_DIALOG')
         && $this->registerHook('displayOrderConfirmation')
         && $this->registerHook('displayBeforeBodyClosingTag')
         );
       
    }
//wywalenie
	public function uninstall()
	{   
		Configuration::deleteByName('GCRS_ENABLED_BADGE');
		Configuration::deleteByName('GCRS_ENABLED_ID');
		Configuration::deleteByName('GCRS_ENABLED_LOG');
		Configuration::deleteByName('GCRS_OPT_IN_STYLE');
		Configuration::deleteByName('GCRS_BADGE_POSITION');


		return parent::uninstall();
		$this->unregisterHook('displayOrderConfirmation');
		$this->unregisterHook('displayBeforeBodyClosingTag');
	}

	/**
	 * Load the configuration form
	 */
	public function getContent()
	{
		/**
		 * If values have been submitted in the form, process.
		 */
		if (((bool)Tools::isSubmit('submitGoogleCustomerReviewsSnippetModule')) == true) {
			$this->postProcess();
		}

		$this->context->smarty->assign('module_dir', $this->_path);

		$output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

		return $output.$this->renderForm();
	}

	/**
	 * Trorzenie formularza konfiguracji modulu 
	 */
	protected function renderForm()
	{
		$helper = new HelperForm();

		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$helper->module = $this;
		$helper->default_form_language = $this->context->language->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitGoogleCustomerReviewsSnippetModule';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
			.'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');

		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFormValues(), /* Dodawanie wartości dla danych wejściowych */
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id,
		);

		return $helper->generateForm(array($this->getConfigForm()));
	}

	/**
	 * Tworzenie struktury formularz.
	 */
	protected function getConfigForm()
	{ //opcjie pozycja plakietki 
		 $badge_position = array(
		    array(
                'id_position' => 'BOTTOM_RIGHT',
                'name' => $this->l('Bottom right'),
            ),
            array(
                'id_position' => 'BOTTOM_LEFT',
                'name' => $this->l('Bottom left'),
            ),
            array(
                'id_position' => 'INLINE',
                'name' => $this->l('Where the badge code'),
            ),
          );
       // opcje pozycji zgody 
		 $opt_in_style = array(
            array(
                'id_opt' => 'CENTER_DIALOG',
                'name' => $this->l('Center dialog'),
            ),
            array(
                'id_opt' => 'BOTTOM_RIGHT_DIALOG',
                'name' => $this->l('Bottom right dialog'),
            ),
            array(
                'id_opt' => 'BOTTOM_LEFT_DIALOG',
                'name' => $this->l('Bottom left dialog'),
            ),
            array(
                'id_opt' => 'TOP_RIGHT_DIALOG',
                'name' => $this->l('Top right dialog'),
            ),
            array(
                'id_opt' => 'TOP_LEFT_DIALOG',
                'name' => $this->l('Top left dialog'),
            ),
            array(
                'id_opt' => 'BOTTOM_TRAY',
                'name' => $this->l('Bottom tray'),
            ),
          );  
		return array(
			'form' => array(
				'legend' => array(
				'title' => $this->l('Settings'),
				'icon' => 'icon-cogs',
				),
				'input' => array(
					array(
						'col' => 8,
						'type' => 'text',
						'prefix' => '<i class="icon icon-barcode"></i>',
						'desc' => $this->l('Enter your Google Merchant ID'),
						'name' => 'GCRS_MERCHANT_ID',
						'label' => $this->l('Merchant ID'),
					),
				 array(
                        'type' => 'select',
                        'label' => $this->l('Place of display dialog box'),
                        'name' => 'GCRS_OPT_IN_STYLE',
                        //'default_value' => $helper->tpl_vars['fields_value']['opt_in_style'],
                        'options' => array(
                            'query' => $opt_in_style,
                            'id' => 'id_opt',
                            'name' => 'name',
                          ),
                          'desc' => $this->l('Specify how the consent module dialog box is displayed')
                        ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Turn on the badge'),
                        'name' => 'GCRS_ENABLED_BADGE',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                        'desc' => $this->l('Choose whether to enable the badge'),
                    ),
                 array(
                        'type' => 'select',
                        'label' => $this->l('Google Customer Reviews badge'),
                        'name' => 'GCRS_BADGE_POSITION',
                        //'default_value' => $helper->tpl_vars['fields_value']['badge_position'],
                        'options' => array(
                            'query' => $badge_position,
                            'id' => 'id_position',
                            'name' => 'name',
                          ),
                          'desc' => $this->l('Specify where you want the badge to appear')
                        ),
				 array(
                        'type' => 'switch',
                        'label' => $this->l('Enable logs'),
                        'name' => 'GCRS_ENABLED_LOG',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                        'desc' => $this->l('Enables logs from generated consent forms'),
                    ),
                   ),
				'submit' => array(
					'title' => $this->l('Save'),
				
			),
		),
		);
	}

	/**
	 * Ustaw wartości weiściowe (wczytaj z konfiguracji)
	 */
	protected function getConfigFormValues()
	{
		return array(
			'GCRS_ENABLED_BADGE' => Configuration::get('GCRS_ENABLED_BADGE'),
			'GCRS_ENABLED_LOG' => Configuration::get('GCRS_ENABLED_LOG'), 
			'GCRS_MERCHANT_ID' => Configuration::get('GCRS_MERCHANT_ID'),
			'GCRS_BADGE_POSITION'=> Configuration::get('GCRS_BADGE_POSITION'),
			'GCRS_OPT_IN_STYLE' => Configuration::get('GCRS_OPT_IN_STYLE')
		);
	}

	/**
	 * Zapisywanie danych z formularza.
	 */
	protected function postProcess()
	{
		$form_values = $this->getConfigFormValues();

		foreach (array_keys($form_values) as $key) {
			Configuration::updateValue($key, Tools::getValue($key));
		}
	}


	public function hookdisplayOrderConfirmation($params)
	{
	 if (Configuration::get('GCRS_MERCHANT_ID')) {
            if (!empty($params['order'])) $order = $params['order'];
                else return false;
        //weź produkty
        $products = $order->getProducts();
        //weź ean 
        $products_ids = $this->getProductsEan($products);   
		// weź adres
		$address = new Address($order->id_address_delivery);
		// weź kraj
		$country = new Country($address->id_country);
        //weź dane klienta 
        $customer = $order->getCustomer();

            $this->context->smarty->assign(array(
				'merchant_id'             => Configuration::get('GCRS_MERCHANT_ID'),
				'order_id'                => $order->reference,
				'customer_email'          => $customer->email,
				'country'                 => $country->iso_code,
				'estimated_delivery_date' => $this->computeDeliveryDate(),
				'gtin'   		          => $this->getProductsEan($products),
				'gdzie_plakietka'		  => Configuration::get('GCRS_OPT_IN_STYLE')
			)
    );

		//$this->computeDeliveryDate();

		$view = $this->context->smarty->fetch($this->local_path . 'views/templates/front/customer_reviews_snippet.tpl');
        if (Configuration::get('GCRS_ENABLED_LOG')==1) $this->logCustomers($view);
		return $view;
	}
}
    public function hookdisplayBeforeBodyClosingTag($params)
    {      
		if (Configuration::get('GCRS_MERCHANT_ID') and Configuration::get('GCRS_ENABLED_BADGE') == 1) {
           $cookie = $this->context->cookie;
           if (isset($cookie->plakietka)) return "<!-- plakietka już '{$cookie->plakietka}' -->";
           // $lang = $this->context->cookie->iso_code_country;
           $lang = $this->context->language->iso_code;
           $cookie->plakietka = 'wyswietlona';    
        
    return "<!-- POCZĄTEK kodu plakietki Opinii konsumenckich Google -->
    			<script src=\"https://apis.google.com/js/platform.js?onload=renderBadge\" async defer>
				</script>
				<script>
  window.renderBadge = function() {var ratingBadgeContainer = document.createElement(\"div\");
    document.body.appendChild(ratingBadgeContainer);
    window.gapi.load('ratingbadge', function() {
      window.gapi.ratingbadge.render(
        ratingBadgeContainer, {
          \"merchant_id\":".Configuration::get('GCRS_MERCHANT_ID').",
          \"position\":\"".Configuration::get('GCRS_BADGE_POSITION')."\",
        });
    });
  }
</script>
<!-- KONIEC kodu plakietki Opinii konsumenckich Google -->
<!-- POCZĄTEK kodu językowego Opinii konsumenckich Google -->
<script>
  window.___gcfg = { lang: '{$lang}' };
</script>
<!-- KONIEC kodu językowego Opinii konsumenckich Google -->";
	
	} else return "<!-- plakietka nie aktywna lub brak id -->";
}
        private function logCustomers($data)
        {
                $now = new DateTime();

                $output = "==========" . $now->format('Y-m-d H:i:s') . "==========\n";
		$output.= $data . "\n";		

		file_put_contents($this->local_path . 'log.txt', $output, FILE_APPEND);
	}
	private function getProductsEan($products)
			{
				$ids = [];
				if (count($products)) {
				foreach ($products as $p) {
            	$ids[] = $p['ean13'];
				
				}
				return '{"gtin":"'. join(', {"gtin":"', $ids).'"}';
			} else {
            return '';
        }
    }
	private function computeDeliveryDate()
	{
		// [1] Poniedziałek -> [7] Niedziela
		$businessDays = array(1, 2, 3, 4, 5 );
		$max = 100;
		$deliveryTime = 2;// czyta teorjia :)
		$dateTime = new DateTime();
		$deliveryDateTime = clone $dateTime;
		while( $deliveryTime )
		{
			$dateTime->add(new DateInterval('P1D'));
			if( in_array( $dateTime->format('N'), $businessDays ) )
				$deliveryTime--;

			$deliveryDateTime->add(new DateInterval('P1D'));
		}
		return $deliveryDateTime->format('Y-m-d');
	}
}
