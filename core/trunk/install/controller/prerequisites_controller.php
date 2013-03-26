<?php
/*
 *   Copyright 2008-2012 Maarch
 *
 *   This file is part of Maarch Framework.
 *
 *   Maarch Framework is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   Maarch Framework is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with Maarch Framework. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @brief class of install tools
 *
 * @file
 * @author Arnaud Veber
 * @date $date$
 * @version $Revision$
 * @ingroup install
 */

//CONTROLLER
    //TITLE
        $shortTitle = _PREREQUISITES;
        $longTitle = _PREREQUISITES;

    //CAN CONTINUE
        $canContinue = $Class_Install->checkAllNeededPrerequisites();

    //PROGRESS
        $stepNb = 4;
        $stepNbTotal = 8;

//VIEW
    $view = 'prerequisites';
