<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Drupal\custom_recording\Plugin\Action;



use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;


/**
 * Some description.
 *
 * @Action(
 *   id = "custom_recording_assign_Term_action",
 *   label = @Translation("Assign Tags to Node"),
 *   type = "node",
 *   confirm = TRUE
 *   
 * )
 */


class AssignTermAction  extends ViewsBulkOperationsActionBase {
  
    
    
  //use StringTranslationTrait;

  
  
  
  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $entities) {
    $ids = [];

    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    
    
    foreach ($entities as $entity) {
                $ids[$entity->id()] = $entity->getEntityTypeId();
                  $title = $entity->getTitle();
                  $arrTags = $this->convertTitleToTag($title);
//
                

                    if(!empty($arrTags)){    
                           // $node = Node::load($entity->id());    
                          //  $node->set('field_tags', ['target_id' => $tid]);  
                           // $node->save();


                          $node = \Drupal::entityTypeManager()->getStorage('node')->load($entity->id());
                          $tids = $entity->get('field_tags')->getValue();
                         // $updatedTerms = [];
                         // $updatedTerms[] = $arrTags;      
                       //   print_r($updatedTerms);die;
                          foreach($tids as $term) {
                           // if ($term['target_id'] != 62)
                            // if(!in_array($term['target_id'], $arrTags))   
                              $arrTags[] = ['target_id' => $term['target_id']];      
                          }
                          
                          $node->field_tags = $arrTags;
                          $node->save();
                      }  
        
    }
    

    
    
    
    
//
//    $entity = reset($entities);
//    if ($entity instanceof EntityInterface) {
//      $entity_type = $entity->getEntityTypeId();
//    }
//    $this->tempStoreFactory->get($this->getTempStoreName($entity_type))
//      ->set($this->currentUser->id(), $ids);
  }

  
  
  
  
  
  /**
   * {@inheritdoc}
   */
  public function execute(ContentEntityInterface $entity = NULL) {
    // Do some processing..
    // Don't return anything for a default completion message, otherwise return translatable markup.
    
    $this->executeMultiple([$entity]);
  }
  
  
 
//    public function __construct(array $configuration, $plugin_id, $plugin_definition,  AccountInterface $current_user) {
//    parent::__construct($configuration, $plugin_id, $plugin_definition);
//
//    $this->currentUser = $current_user;
//    //$this->tempStoreFactory = $temp_store_factory;
//  }

    
    /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    if ($object->getEntityType() === 'node') {
      $access = $object->access('update', $account, TRUE)
        ->andIf($object->status->access('edit', $account, TRUE));
      return $return_as_object ? $access : $access->isAllowed();
    }

    // Other entity types may have different
    // access methods and properties.
    return TRUE;
  }
  
  
  public function convertTitleToTag($title=''){
      $tagArr = [];
      if(!empty($title)){
          $data = $this->extractCommonWords($title);
          
          if(!empty($data)){               
            foreach($data as $key => $value){               
                $query = \Drupal::entityQuery('taxonomy_term');
                $query->condition('vid', "tags");
                $query->condition('name', $key);
                $query->range(0,1);
                $tids = $query->execute();   

                if(empty($tids)){    
                    $term = \Drupal\taxonomy\Entity\Term::create([
                              'vid' => 'tags',
                              'name' => $key,
                        ]);
                  $term->save();
                  $tagArr[]['target_id'] = $term->id();      
                }else{
                    reset($tids);
                    $tagArr[]['target_id']  =  current($tids);   
                }
            }
          }
       
      }            
      
      //print_r($tagArr);die;
      return $tagArr;
  }
  
  
  public function extractCommonWords($string){
      
      $stopWords = array('i','a','about','an','and','are','as','at','be','by','com','de','en','for','from','how','in','is','it','la','of','on','or','that','the','this','to','was','what','when','where','who','will','with','und','the','www'); 
      $string = preg_replace('/\s\s+/i', '', $string); // replace whitespace
      $string = trim($string); // trim the string
      $string = preg_replace('/[^a-zA-Z0-9 -]/', '', $string); // only take alphanumerical characters, but keep the spaces and dashes too…
      $string = strtolower($string); // make it lowercase
 
      preg_match_all('/\b.*?\b/i', $string, $matchWords);
      $matchWords = $matchWords[0];
 
      foreach ( $matchWords as $key=>$item ) {
          if ( $item == '' || in_array(strtolower($item), $stopWords) || strlen($item) <= 3 ) {
              unset($matchWords[$key]);
          }
      }   
      $wordCountArr = array();
      if ( is_array($matchWords) ) {
          foreach ( $matchWords as $key => $val ) {
              $val = strtolower($val);
              if ( isset($wordCountArr[$val]) ) {
                  $wordCountArr[$val]++;
              } else {
                  $wordCountArr[$val] = 1;
              }
          }
      }
      arsort($wordCountArr);
      $wordCountArr = array_slice($wordCountArr, 0, 10);
      return $wordCountArr;
}

  
  
  
}
