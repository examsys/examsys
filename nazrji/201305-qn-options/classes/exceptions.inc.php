<?php
// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* Custom exception classes
* 
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

class ValidationException extends Exception { }

class DataTypeException extends Exception { }

class RecordNotFoundException extends Exception { }

class DatabaseException extends Exception { }

class ClassNotFoundException extends Exception { }

class MethodNotImplementedException extends Exception { }

class RandomQuestionNotFound extends Exception { }
