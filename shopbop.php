<?php
/*
 *Prestashop-scraping-product-programmatically
 *author: Eduardo Soliz Valdez
 *version prestashop:1.6
 *description: Scraping product shopbop to prestashop
 *More information for scraping:http://simplehtmldom.sourceforge.net/manual_api.htm
 *possible errors http://www.canbike.org/information-technology/php-fatal-error-call-to-a-member-function-find-on-a-non-object.html
 *limitations server configure ini_set("memory_limit","512M");
 links:http://mypresta.eu/en/art/developer/new-field-product-backoffice.html,http://strife.pl/2011/12/how-to-add-new-custom-field-in-prestashop/
*/

//ALTER TABLE `ps_product` ADD `brandname` VARCHAR(120) ALTER TABLE `ps_product` ADD `imageproduc` TEXT ALTER TABLE `ps_product` ADD `sizefit` TEXT
include('simple_html_dom.php');
//import funtions prestashop
require('../config/config.inc.php');

$menusnot = array('What\'s New','Lookbooks','My Shopbop');
//$menusnot = array('Lookbooks','My Shopbop');
$html = file_get_html('http://www.shopbop.com/');

global $menuid;
global $idlevelone; 
global $idleveltwo; 



foreach ($html->find('#topNavTile #navList li') as $topmenunav) { 
	//foreach ($topmenunav->find('#navList li') as $menu) {
		
			foreach ($topmenunav->find('a.topnav-logged-in') as $a) {

				//Main menu
				$menutext = trim(strip_tags($a->innertext)); 
				echo "<pre>";
				echo "<h1>".$menutext ."</h1><br>";
				echo "</pre>";
			//}
		    if(!in_array($menutext, $menusnot)){
				$menuid = createCategory($menutext,"metatitle", "metadescrip","metakey");
			foreach ($topmenunav->find('id$="submenu"  > li > a') as $submenu) {

				//level one
				$textsubmenu = trim(strip_tags($submenu->innertext));
				echo "<pre>";				
				echo "<h2>".$textsubmenu ."</h2><br>";
					$linksubmenu = $submenu->href;
					$submenulefone = file_get_html('http://www.shopbop.com'.$linksubmenu);
						//metas
						
						$metatitle = $submenulefone->find('head title ', 0)->innertext;
						$metadescrip = $submenulefone->find('head meta[name=description] ["content"]', 0)->content; 
						$metakey = $submenulefone->find('head meta[name=keywords] ["content"]', 0)->content;

						echo "link ".'http://www.shopbop.com'.$linksubmenu;
						echo "       metatitle ".$metatitle;


						//add level one categorys
						$idlevelone = createCategory($textsubmenu,$metatitle, $metadescrip,$metakey,$menuid);	

						//createProduct
						createProductPerPage($submenulefone,$linksubmenu,$idlevelone);

						
						echo "idmenu ....".$menuid;echo "<br>";
						echo "menuid_____ ".$idlevelone;

									
					  foreach ($submenulefone->find('#leftNavigation  li[data-at=leftnav-subcategory] a') as $topmenulef) {
					     	//Level two
					     	$texttopmenulef = trim(strip_tags($topmenulef->innertext));
						    echo "<h3>".$texttopmenulef . '</h3><br>';
					    	$levelthree = $topmenulef->href;						
							//Level three
							$deepcategory = file_get_html('http://www.shopbop.com'.$levelthree);

							//metas
							$metatitletwo = $deepcategory->find('head title ', 0)->innertext;
							$metadescriptwo = $deepcategory->find('head meta[name=description] ["content"]', 0)->content; 
							$metakeytwo = $deepcategory->find('head meta[name=keywords] ["content"]', 0)->content;

							echo "link2 ".'http://www.shopbop.com'.$levelthree;
						    echo "metatitle ".$metatitletwo;

							//add level two
							$idleveltwo = createCategory($texttopmenulef,$metatitletwo." dos", $metadescriptwo,$metakeytwo,$idlevelone);

							//createProduct
					     	createProductPerPage($deepcategory,$levelthree,$idleveltwo);

							echo "idlevelone ....".$idlevelone;echo "<br>";
							echo "two_____ ".$idleveltwo;

							foreach ($deepcategory->find('#leftNavigation  li[data-at=leftnav-deep-category] a') as $deepthree) {
								$textdeep = trim(strip_tags($deepthree->innertext));
								echo "<h4>".$textdeep . '</h4><br>';
								$leveldeephref = $deepthree->href;
								$deepcategorymetas = file_get_html('http://www.shopbop.com'.$leveldeephref);

								//metas
								$metatitle = $deepcategorymetas->find('head title ', 0)->innertext;
								$metadescrip = $deepcategorymetas->find('head meta[name=description] ["content"]', 0)->content; 
								$metakey = $deepcategorymetas->find('head meta[name=keywords] ["content"]', 0)->content;

								echo "link3 ".'http://www.shopbop.com'.$leveldeephref;
						   		echo "metatitle ".$metatitle;


								$idleveldeep = createCategory($textdeep,$metatitle, $metadescrip,$metakey,$idleveltwo);

								//createProduct
					     		createProductPerPage($deepcategorymetas,$leveldeephref,$idleveldeep);


								echo "idlevelDeep ....".$idleveltwo;echo "<br>";
								echo "two_____ ".$idleveldeep;
							}
						
					}					

				echo "</pre>"; 
		} 
	  }
	}
}

function createProductPerPage($linkMenu,$href,$categoria)
{
	//products 							
			$html = $linkMenu;
			$datatotalcount = $html->find('#product-count', 0)->innertext;
			$totalcount = explode(" ", $datatotalcount);
			$totalcount = ceil($totalcount['0']/40);
			$countpage = 0;
			for ($i=0; $i < $totalcount; $i++) { 							
				$producperpage = file_get_html('http://www.shopbop.com'.$href.'?baseIndex='.$countpage);														
				echo "...contandor...".$countpage;echo "<br>";								
				foreach ($producperpage->find('#product-container < li') as $a) {
					$urlDetalleProduc = $a->find('a.photo');
						
				      //Product soldout
					  if(strpos($urlDetalleProduc['0']->href, 'www.shopbop.com') !== false){
							echo "soldout";
					  }else{
								$producdetail = file_get_html('http://www.shopbop.com'.$urlDetalleProduc['0']->href);
							//method_exists($html,"find")
							if(is_object($producdetail)){								
							
								//Product Id shopbop save to  
								
								$datascaping = 'data-productid';
								$idshopbop = $a->$datascaping;
								//Images
								$urlImagen = $a->find('a > span > img');
								$imgProduc = $urlImagen['0']->src;
								

								$serimage = array();
								//middle image
								$urlImagenMiddle = $producdetail->find('#productImageContainer #productZoomImage img',0)->src;							
								$arrayImagenMiddle['media'] = $urlImagenMiddle;
								$large =  str_replace("._QL90_UX336_","",$urlImagenMiddle);
								$arrayImagenMiddle['large'] = $large;
								array_push($serimage, $arrayImagenMiddle);
								//carusel							
								foreach ($producdetail->find('#thumbnailList li:not(#thumbnailVideoListItem) img') as  $img) {
									$arraytemp = array();
									$finded = strpos($img->class,"videoThumb hover showVideo");
									if ($finded === false ) {
										$media =  str_replace("UX37","UX336",$img->src);
										$arraytemp['media'] = $media;
										$arraytemp['thumbnail'] = $img->src ;
									
										array_push($serimage, $arraytemp);							
									}									
								}


						
								//End Images Scraping
								$nameProduc = $producdetail->find('#product-information[itemprop=name]', 0)->innertext;
								$brandProduc = $producdetail->find('#product-information a[itemprop=brand]', 0)->innertext;
								$descripProduc = $producdetail->find('#right-column [itemprop="description"] ', 0)->innertext;
								$sizefitProduc = $producdetail->find('#sizeFitContainer', 0)->innertext;

								$priceBlack = $producdetail->find('#product-information #productPrices meta[itemprop=price] ["content"]', 0)->content;

								//prices black red association with texture
								$pricesAll = array();
								foreach ($producdetail->find('#product-information #productPrices .priceBlock') as $detail) {
									$priceRed = array();
									foreach ($detail->find('span') as $value) {
										if ($value->class=='salePrice') {
											$priceRed['pricered'] = $value->innertext;
										}
										if ($value->class=='priceColors') {
											$priceRed['priceColors'] = $value->innertext;
										}									
									}
									array_push($pricesAll, $priceRed);								
								}							
								unset($pricesAll['0']);
																
								$arrayPricesAll=array();
								//More legible 
								foreach ($pricesAll as $values) {
									
									$otro="";
									foreach ($values as  $cadenaexplotear) {
										$otro .= $cadenaexplotear."@";	
									}
									array_push($arrayPricesAll, $otro);
														
								}	
															
								//End scraping prices


								//metas products
								$metatitle = $producdetail->find('head title ', 0)->innertext;
								$metadescrip = $producdetail->find('head meta[name=description] ["content"]', 0)->content; 
								$metakey = $producdetail->find('head meta[name=keywords] ["content"]', 0)->content;
								//end metas products						
								 
								
								$sizesProduc = array();
								foreach ($producdetail->find('#product-information #sizes span') as $sizes) {										
									array_push($sizesProduc,$sizes->innertext);										
								}
								//images textures
								$texturesProduc = array();
								$arrayTexturesPrices = array();
								foreach ($producdetail->find('#product-information #swatches img') as $imgsTex) {
									array_push($texturesProduc,$imgsTex->src);

									//Prices and textures															
									foreach ($arrayPricesAll as $value) {
										$cadena = explode("@", $value);
										$namesTitles = explode(",", $cadena['1']);									
										$namesTitles=array_map('trim',$namesTitles);									
										if (in_array($imgsTex->title, $namesTitles)) {
											$price = str_replace('$','',$cadena['0']);
											array_push($arrayTexturesPrices, $price);										 
										}
									}																								
								}	

								echo "<b> id Shopbop </b>".$idshopbop;echo "<br>";	
								echo "<b>name Product </b>".$nameProduc;echo "<br>";	
								echo "<b>price Product </b>".$priceBlack;	echo "<br>";
								echo "<b>Description Product </b>".$descripProduc;	echo "<br>";
								echo "<b>Imagen vist prev </b>".$imgProduc;	echo "<br>";
								
								
								
								$existPresta = existsRefInDatabaseTwo($idshopbop); 

								if ($existPresta != false) {
									updateproduct($existPresta,$categoria);
								}else{
									createProduc(trim($nameProduc),trim($brandProduc),trim($imgProduc),$serimage,$idshopbop,$categoria,$priceBlack,$arrayTexturesPrices,$descripProduc,$sizesProduc,$texturesProduc,$metatitle,$metadescrip,$metakey,$sizefitProduc);
								}
							}//Check product accessible
					 }//end soldout Product 						     	
			     	
				}
				sleep(5);
				$countpage = $countpage+40;
				$producdetail->clear();								
								 $producperpage->clear();	
			}
		
		//end products level one
}

//return id to category
function createCategory($name, $metatitle, $metadescrip, $metakey,$parent)
{
	$context = Context::getContext();
	$cat = new Category (null,Context::getContext()->language->id);

	if (!empty($name)) {
		$cat->name = $name;
	} else {
		$cat->name = "TopSellersImage";
	}
	

	$cat->meta_title =  $metatitle;
	$cat->meta_description = $metadescrip;
	$cat->meta_keywords = $metakey;	

	$cat->active = 1;
	$cat->link_rewrite = Tools::link_rewrite($cat->name);
	if (isset($parent)) {		
		$cat->id_parent = $parent;
	}else{		
		$cat->id_parent = Configuration::get('PS_HOME_CATEGORY');
	}
	
	$cat->add();

	return $cat->id;
}
//return id to category

function createProduc($name,$brand,$images,$imageall,$idshopbop,$category,$price,$priceAllRed,$descrip,$sizes,$textures,$metatitle,$metadescrip,$metakey,$sizefit)
{
	$context = Context::getContext();
	$pro = new Product (null,false,Context::getContext()->language->id);

	$pro->price = $price;
	$pro->id_tax_rules_group = 0;
	$name = preg_replace('/[^A-Za-z0-9\-]/', ' ', $name);
	$pro->name = $name;
	$pro->brandname = $brand;
	$pro->imageproduc = $images;
	$pro->reference = $idshopbop;
	$pro->description = $descrip;
	$pro->meta_title = $metatitle;
	$pro->meta_description = $metadescrip;
	$pro->meta_keywords = $metakey;
	echo "<br> Aqui sizefit ".$sizefit." <br>";
	$pro->sizefit = $sizefit;
	$pro->id_manufacturer = 0;
	$pro->id_supplier = 0;
	$pro->quantity = 1;
	$pro->minimal_quantity = 1;
	$pro->additional_shipping_cost = 0; 
	$pro->wholesale_price = 0;
	$pro->ecotax = 0;
	$pro->width = 0;
	$pro->height = 0;
	$pro->depth = 0;
	$pro->weight = 0;
	$pro->out_of_stock = 0;
	$pro->active = 1;
	$pro->id_category_default = $category;
	$pro->category = $category;
	$pro->available_for_order = 0;
	$pro->show_price = 1;
	$pro->on_sale = 0;
	$pro->online_only = 1;
    $pro->link_rewrite = Tools::link_rewrite($pro->name);	
	$pro->add();
	 //ps_category_product getCategories()
    $pro->addToCategories(array($category));

    //Add attributes sizes
    foreach ($sizes as $value) { 
   		 //Much prices here fix
  		  $pro->addAttribute(0, 0, 0, 0, null, "reference", null,true, null, null,  1, array(), null);	   
	      //$pro->addAttributeCombinaison($idsAtrib['0']['id_product_attribute'], $idattr);
    }
     $idsAtrib = $pro->getProductAttributesIds($pro->id,false);
     $conta = count($idsAtrib);
     for ($i=0; $i < $conta; $i++) {
     	echo "idproduc ".$idsAtrib[$i]['id_product_attribute'];
     	 //echo "id name ".getNameAtt($sizes[$i],1);echo "<br>";
     	 $sql = "INSERT INTO ps_product_attribute_combination (id_attribute, id_product_attribute) VALUES ('".getNameAtt($sizes[$i],1)."', '".$idsAtrib[$i]['id_product_attribute']."')";
     	 Db::getInstance()->executeS($sql);
     }//End Sizes 
    

    //Add attributes textures    
    $texturesarrayids = array();
    $countRedPrice = count($priceAllRed);

    if ($countRedPrice == 0) {
    	foreach ($textures as $value) {  
   		 //Much prices here fix   		
  		 $ids = $pro->addAttribute(0, 0, 0, 0, null, "referencetextures", null,true, null, null,  1, array(), null);	   
	      array_push($texturesarrayids, $ids);
  		}
    } else {
    	 foreach ($priceAllRed as $value) {   
   		 //Much prices here fix
   		 $totalprice = $price-$value; 
   		 echo "Total ".$totalprice." <br>";
  		 $ids = $pro->addAttribute(-$totalprice, 0, 0, 0, null, "referencetextures", null,true, null, null,  1, array(), null);	   
	      array_push($texturesarrayids, $ids);
  		}
    }//end attributes textures    
    
   

     //$idsAtrib = $pro->getProductAttributesIds($pro->id,false);
     $conta = count($texturesarrayids);    

     for ($i=0; $i < $conta; $i++) {
     	echo " Atrubuto texture idproduc ".$texturesarrayids[$i];
     	 //echo "Atrubuto texture id name ".getNameAtt($textures[$i],3);echo "<br>";
     	 $sql = "INSERT INTO ps_product_attribute_combination (id_attribute, id_product_attribute) VALUES ('".getNameAtt($textures[$i],3)."', '".$texturesarrayids[$i]."')";
     	 Db::getInstance()->executeS($sql);
     }//End textures 
     	
     //images all
     foreach ($imageall as $value) {
     	
     		$shops = Shop::getShops(true, null, true); 
			$image = new Image(null,Context::getContext()->language->id);
            $image->id_product = $pro->id;
            $image->position = Image::getHighestPosition($pro->id) + 1;
            if ($value === reset($imageall)){
            	 $image->cover = true; // or false;
            }else{
            	$image->cover = false; // or false;
            }                        
            $image->pathurl = serialize($value); 
            $image->associateTo($shops);
            $image->add();
     }			
      //end images all     
	return $pro->id;
}

//greate group 
function createGroup($type)
{
				
			    $newGroup = new AttributeGroup(null,Context::getContext()->language->id);
	            $newGroup->name = 'Numeric';
	            $newGroup->AttributeGroup->name = 'Numeric';
	            $newGroup->public_name = 'Numeric';
	            $newGroup->is_color_group = 0;
	            $newGroup->group_type = 'select';
	            $newGroup->position = AttributeGroupCore::getHigherPosition() + 1;
				$newGroup->add();
				return $newGroup->id;

}
//create Attibute in A group ones  create and get Field sizes
function createAttribute($valueAtribute,$type)
{
			$newAttribute = new Attribute(null,Context::getContext()->language->id);
			$newAttribute->name = $valueAtribute;
			if ($type == 1) {
				$newAttribute->id_attribute_group = 1;
			}elseif($type == 3){
				$newAttribute->id_attribute_group = 3;
			}
			

			$newAttribute->add();

			return $newAttribute->id;
}

//Return Id Atribute
function getNameAtt($name, $type)
{
	$result = false;
	$attribute = new Attribute(null,Context::getContext()->language->id);
    $mirar = $attribute->getAttributes(Context::getContext()->language->id);

    foreach ($mirar as $value) {
    	if ($name == $value['name']) {    		
    		$result =  $value['id_attribute'];
    		return $result;
    	}  
    } 
    if ($result == false) {
    	$result = createAttribute($name,$type);
    	return $result;
    }   
}
//Exit Produc find reference return id product
function existsRefInDatabaseTwo($reference)
{
	$result = false;
		$row = Db::getInstance()->getRow('
		SELECT `reference`, `id_product`
		FROM `'._DB_PREFIX_.'product` p
		WHERE p.reference = "'.pSQL($reference).'"');

		if ($row['reference']) {
			$result = $row['id_product'];
		}

		return $result;
}
function updateproduct($idprduc,$category)
{
	echo "Actualizando el producto ".$idprduc;
	$context = Context::getContext();
	$pro = new Product ($idprduc,false,Context::getContext()->language->id);	
	$pro->addToCategories(array($category));
	$pro->update();	
}

?>