<?php

namespace MdsSupportingClasses;

use MdsExceptions\InvalidResourceDataException;

class MdsCheckoutFields
{
	/**
	 * @var MdsColliveryService
	 */
	protected $service;

	/**
	 * @var Collivery
	 */
	protected $collivery;

	/**
	 * @var MdsCache
	 */
	protected $cache;

	/**
	 * @var array
	 */
	protected $defaultFields = [];

	/**
	 * CheckoutFields constructor.
	 * @param array $defaultFields
	 */
	public function __construct(array $defaultFields)
	{
		$this->service = MdsColliveryService::getInstance();
		$this->collivery = $this->service->returnColliveryClass();
		$this->cache = $this->service->returnCacheClass();
		$this->defaultFields = $defaultFields;
	}

	/**
	 * @param string|null $prefix
	 * @return array
	 */
	public function getFields($prefix = null)
	{
		if(!$this->service->isEnabled()) {
			if($prefix && isset($this->defaultFields[$prefix])) {
				return $this->defaultFields[$prefix];
			} else {
				return $this->defaultFields;
			}
		}

		if($prefix) {
			$prefix = $prefix . '_';
		}

		try {
			$resources = $this->getResources();
			$towns = ['' => 'Select Town'] + $resources['towns'];
			$location_types = ['' => 'Select Premises Type'] + $resources['location_types'];

			return array(
				$prefix . 'country' => array(
					'type' => 'select',
					'label' => 'Country',
					'required' => true,
					'class' => array( 'form-row-wide', 'address-field', 'update_totals_on_change' ),
					'autocomplete' => 'country',
				),
				$prefix . 'state' => array(
					'type' => 'select',
					'label' => 'City/Town',
					'required' => true,
					'class' => array( 'form-row-wide', 'address-field', 'update_totals_on_change' ),
					'options' => $towns,
					'selected' => ''
				),
				$prefix . 'city' => array(
					'type' => 'select',
					'label' => 'Suburb',
					'required' => true,
					'class' => array( 'form-row-wide', 'address-field' ),
					'options' => array( 'Select town first...' )
				),
				$prefix . 'location_type' => array(
					'type' => 'select',
					'label' => 'Location Type',
					'required' => true,
					'class' => array( 'form-row-wide', 'address-field', 'update_totals_on_change' ),
					'options' => $location_types,
					'selected' => ''
				),
				$prefix . 'company' => array(
					'label' => 'Company Name',
					'placeholder' => 'Company (optional)',
					'autocomplete' => 'organization',
					'class' => array( 'form-row-wide' )
				),
				$prefix . 'address_1' => array(
					'label' => 'Street',
					'placeholder' => 'Street number and name.',
					'autocomplete' => 'address-line1',
					'required' => true,
					'class' => array( 'form-row-wide' )
				),
				$prefix . 'address_2' => array(
					'label' => 'Building Details',
					'placeholder' => 'Apartment, suite, unit etc. (optional)',
					'class' => array( 'form-row-wide' ),
					'autocomplete' => 'address-line2',
					'required' => false,
				),
				$prefix . 'postcode' => array(
					'label' => 'Postal Code',
					'placeholder' => 'Postal Code',
					'required' => false,
					'class' => array( 'form-row-wide' )
				),
				$prefix . 'first_name' => array(
					'label' => 'First Name',
					'placeholder' => 'First Name',
					'autocomplete' => 'given-name',
					'required' => true,
					'class' => array( 'form-row-first' )
				),
				$prefix . 'last_name' => array(
					'label' => 'Last Name',
					'placeholder' => 'Last Name',
					'autocomplete' => 'family-name',
					'required' => true,
					'class' => array( 'form-row-last' )
				),
				$prefix . 'phone' => array(
					'validate' => array( 'phone' ),
					'label' => 'Cell Phone',
					'placeholder' => 'Phone number',
					'required' => true,
					'class' => array( 'form-row-wide' )
				),
				$prefix . 'email' => array(
					'validate' => array( 'email' ),
					'label' => 'Email Address',
					'placeholder' => 'you@yourdomain.co.za',
					'required' => true,
					'class' => array( 'form-row-wide' )
				),
			);
		} catch(InvalidResourceDataException $e) {
			return $this->defaultFields;
		}
	}

	/**
	 * @param int $attempt
	 * @return array
	 * @throws InvalidResourceDataException
	 */
	private function getResources($attempt = 0)
	{
		if($attempt > 1) {
			throw new InvalidResourceDataException($this->service, "Unable to retrieve fields from the API");
		}

		$towns = $this->collivery->getTowns();
		$location_types = $this->collivery->getLocationTypes();

		if(empty($towns) || empty($location_types)) {
			$this->cache->clear('town');
			$this->cache->clear('location_types');
			$this->getResources($attempt + 1);
		} else {
			$towns = array_combine($towns, $towns);
			$location_types = array_combine($location_types, $location_types);
			return compact('towns', 'location_types');
		}
	}
}
