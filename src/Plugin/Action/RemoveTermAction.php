<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * @ Developer: Mohit Gupta (csmohitgupta@gmail.com)
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
 *   id = "custom_recording_remove_term_action",
 *   label = @Translation("Remove Tags From Node"),
 *   type = "node",
 *   confirm = TRUE
 *
 * )
 */
class RemoveTermAction extends ViewsBulkOperationsActionBase {

    /**
     * {@inheritdoc}
     */
    public function executeMultiple(array $entities) {
        if (!empty($entities)) {
            foreach ($entities as $entity) {
                $ids[$entity->id()] = $entity->getEntityTypeId();
                if ($entity) {
                    if (!$entity->hasField('field_tags')) {
                        throw new \RuntimeException("News migration field not found on node.");
                    }
                    $entity->set('field_tags', []);
                    $entity->save();
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function execute(ContentEntityInterface $entity = NULL) {
        $this->executeMultiple([$entity]);
    }

    /**
     * {@inheritdoc}
     */
    public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
        if ($object->getEntityType() === 'node') {
            $access = $object->access('update', $account, TRUE)
                    ->andIf($object->status->access('edit', $account, TRUE));
            return $return_as_object ? $access : $access->isAllowed();
        }
        return TRUE;
    }

}
