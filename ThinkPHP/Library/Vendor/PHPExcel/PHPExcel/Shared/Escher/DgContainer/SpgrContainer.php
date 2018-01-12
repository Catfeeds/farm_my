<?php

/**
 * PHPExcel_Shared_Escher_Dgforeach_Spgrforeach
 *
 * Copyright (c) 2006 - 2015 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    PHPExcel_Shared_Escher
 * @copyright  Copyright (c) 2006 - 2015 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 * @version    ##VERSION##, ##DATE##
 */
class PHPExcel_Shared_Escher_Dgforeach_Spgrforeach
{
    /**
     * Parent Shape Group foreach
     *
     * @var PHPExcel_Shared_Escher_Dgforeach_Spgrforeach
     */
    private $parent;

    /**
     * Shape foreach collection
     *
     * @var array
     */
    private $children = array();

    /**
     * Set parent Shape Group foreach
     *
     * @param PHPExcel_Shared_Escher_Dgforeach_Spgrforeach $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * Get the parent Shape Group foreach if any
     *
     * @return PHPExcel_Shared_Escher_Dgforeach_Spgrforeach|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add a child. This will be either spgrforeach or spforeach
     *
     * @param mixed $child
     */
    public function addChild($child)
    {
        $this->children[] = $child;
        $child->setParent($this);
    }

    /**
     * Get collection of Shape foreachs
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Recursively get all spforeachs within this spgrforeach
     *
     * @return PHPExcel_Shared_Escher_Dgforeach_Spgrforeach_Spforeach[]
     */
    public function getAllSpforeachs()
    {
        $allSpforeachs = array();

        foreach ($this->children as $child) {
            if ($child instanceof PHPExcel_Shared_Escher_Dgforeach_Spgrforeach) {
                $allSpforeachs = array_merge($allSpforeachs, $child->getAllSpforeachs());
            } else {
                $allSpforeachs[] = $child;
            }
        }

        return $allSpforeachs;
    }
}