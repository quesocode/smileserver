<?php
/*
 *  $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.phpdoctrine.org>.
 */

/**
 * Easily create a slug for each record based on a specified set of fields
 *
 * @package     Doctrine
 * @subpackage  Template
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 */
class Doctrine_Template_Listener_Hashable extends Doctrine_Record_Listener
{
    /**
     * Array of sluggable options
     *
     * @var string
     */
    protected $_options = array();

    /**
     * __construct
     *
     * @param string $array
     * @return void
     */
    public function __construct(array $options)
    {
        $this->_options = $options;
    }

    /**
     * Set the hash value automatically when a record is inserted
     *
     * @param Doctrine_Event $event
     * @return void
     */
    public function postInsert(Doctrine_Event $event)
    {
        $record = $event->getInvoker();
        $name = $record->getTable()->getFieldName($this->_options['name']);

        if ( ! $record->$name) {
            $record->$name = $this->buildHashFromId($record);
        }
        $record->save();
    }

    

    /**
     * Generate the slug for a given Doctrine_Record based on the configured options
     *
     * @param Doctrine_Record $record
     * @return string $slug
     */
    protected function buildHashFromId($record)
    {

      return PseudoCrypt::udihash($record->id);

    	
    }

    

    
}

class PseudoCrypt {
 
  private static $golden_primes = array(
    36 => array(1,23,809,28837,1038073,37370257 /*,1345328833*/)
  );
 
  public static function udihash($num, $len = 5, $base = 36) {
    $gp = self::$golden_primes[$base];
    $maxlen = count($gp);
    $len = $len > ($maxlen-1) ? ($maxlen-1) : $len;
    while($len < $maxlen && pow($base,$len) < $num) $len++; 
    if($len >= $maxlen) throw new Exception($num." out of range (max ".pow($base,$maxlen-1).")");
    $ceil = pow($base,$len);
    $prime = $gp[$len];
    $dechash = ($num * $prime) % $ceil;
    $hash = base_convert($dechash, 10, $base);
    return str_pad($hash, $len, "0", STR_PAD_LEFT);
  }
 
}