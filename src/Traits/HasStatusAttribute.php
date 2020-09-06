<?php
namespace Revext\Repository\Traits;

/**
 * Class RepositoryException
 * @package Revext\Repository\Exceptions
 * @author Gellért Guzmics <guzmics.gellert@revext.com>
 */
trait HasStatusAttribute
{
    protected $statusAttribute = null;
    protected $deletedStatusVal = null;
    protected $restoredStatusVal = null;

    static public function getStatusAttribute(){
        return $this->statusAttribute ?? config('repository.status.attribute');
    }

    static public function getDeletedStatusValue(){
        return $this->statusAttribute ?? config('repository.status.deleted_val');
    }

    static public function getRestoredStatusValue(){
        return $this->statusAttribute ?? config('repository.status.restored_val');
    }
}