public function propertiesOut($id=false, $code=false)
	{
		if($id != false) {
	
			$this->propsIdOut = $id;
		}
		if(!CModule::IncludeModule("iblock")) return false;
		if(empty($this->propsIdOut)) return false;
			global $USER;

			$arSelect = array('ID', 'IBLOCK_ID');
			$arFilter = array("ID" =>  $this->propsIdOut);

			$res = CIBlockElement::GetList(
			    array("ID" => "ASC"),
			    $arFilter,
			    false,
			    false,
			    $arSelect
			    );
			  
			  while($ob = $res->GetNextElement())
			    {
			    	$arFields = $ob->GetFields();
				    $arIdProp[$arFields['ID']] = $ob->GetProperties($arOrder,array("CODE" => $code));
			    }
		//получаем все значения поставщика
		foreach ($arIdProp as $ProdId => $PropCode) {
			foreach ($PropCode as $PropKey => $PropVal) {
				if($PropVal['CODE'] == 'IB_PROVIDER') $xmlid[] = $PropVal['VALUE'];
			}
		}
		$this->HLload(self::providersHL);
		$rsData = $this->strEntityDataClass::getList(array(
			'filter' => array('UF_XML_ID' => $xmlid),
			'select' => array('UF_NAME', 'ID', 'UF_XML_ID', 'UF_SORT'),
			'order' => array('ID' => 'DESC')
		));

		while($arItem = $rsData->Fetch()) {
			$arProv[$arItem['UF_XML_ID']] = $arItem;
		}

		$this->propsIdOut = NULL;
		if(!empty($arIdProp)) {
			foreach ($arIdProp as $ProdId => $PropCode) {
				foreach ($PropCode as $PropKey => $PropVal) {
					if($PropVal['CODE'] == 'PROVIDER'){
			    		unset($arIdProp[$ProdId][$PropKey]);
			    	}elseif($PropVal['CODE'] == 'IB_PROVIDER'){
			    		$arIdProp[$ProdId][$PropKey]['CODE'] = 'PROVIDER';
						$arIdProp[$ProdId][$PropKey]['VALUE'] = $arProv[$PropVal['VALUE']];
			    	}elseif($PropVal['PROPERTY_TYPE'] == 'F'){
			    		if($PropVal['MULTIPLE'] == 'Y'){
			    			if($PropVal['VALUE']){
				    			foreach ($PropVal['VALUE'] as $key => $val) {
				    				$arIdProp[$ProdId][$PropKey]['VALUE'][$key] = CFile::GetPath($val);
				    			}
			    			}
			    		} else {
							$arIdProp[$ProdId][$PropKey]['VALUE'] = CFile::GetPath($PropVal['VALUE']);
			    		}				    		
			    	}
				}
			}

			$Property = $this->send("set", "properties", $arIdProp);

		}


		if(!empty($Property)) {
			$PROPERTY_CODE = "E_DATE";
			$PROPERTY_VALUE = date('d.m.Y H:i:s', time() + 1);
			CIBlockElement::SetPropertyValuesEx($key, false, array($PROPERTY_CODE => $PROPERTY_VALUE));
		}

			$result = 'Выгружены свойства элемента каталога на '.$this->siteURL;
			$errors = 'Что то пошло не так, при выгрузке свойства элемента каталога на '.$this->siteURL;

		unset($arIdProp);
		if(!empty($Property)) return $Property; /*else return $errors;*/
	}
