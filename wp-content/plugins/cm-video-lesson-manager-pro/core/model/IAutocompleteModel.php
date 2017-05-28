<?php

namespace com\cminds\videolesson\model;

interface IAutocompleteModel {
	
	function getAutocompleteResults($search, $orderby, $order, $limit);
	function getId();
	function getName();
	
}