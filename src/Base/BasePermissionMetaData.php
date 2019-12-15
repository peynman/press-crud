<?php

namespace Larapress\CRUD\Base;

trait BasePermissionMetaData
{
    public function getViewPermission()
    {
        return $this->getPermissionByVerbName(self::VIEW);
    }

    public function getEditPermission()
    {
        return $this->getPermissionByVerbName(self::EDIT);
    }

    public function getCreatePermission()
    {
        return $this->getPermissionByVerbName(self::CREATE);
    }

    public function getDeletePermission()
    {
        return $this->getPermissionByVerbName(self::DELETE);
    }

    public function getViewReportsPermission()
    {
        return $this->getPermissionByVerbName(self::REPORTS);
    }
}
