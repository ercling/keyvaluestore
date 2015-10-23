<?php
namespace keyvaluestore;

use keyvaluestore\exceptions\FiletypeNotSupportedException;
use keyvaluestore\exceptions\FormatNotSupportedException;
use keyvaluestore\exceptions\WrongTypeException;


/**
 * Storage for instances of Pair
 */
class Store implements \Countable, \Iterator, \ArrayAccess
{
    protected $_store = [];
    protected $_position = 0;

    /**
     * Returns value from store by key
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return isset($this->_store[$key])?$this->_store[$key]:null;
    }

    /**
     * Check data type and add it to store
     * @param Pair|number|string $data Data to add to store
     * @param null $format
     * @throws FiletypeNotSupportedException
     */
    public function push($data, $format=null)
    {
        if ($data instanceof Pair){
            $this->pushPair($data);
        }elseif (is_array($data)){
            $this->pushArray($data);
        }elseif (is_file($data)){
            if ($format === null){
                $info = new \SplFileInfo($data);
                $extension = $info->getExtension();
                $format = $extension;
            }
            switch (mb_strtolower($format)){
                case 'xml':
                    $this->pushXmlFile($data);
                    break;
                case 'ini':
                    $this->pushIniFile($data);
                    break;
                default:
                    throw new FiletypeNotSupportedException();
            }
        }
    }

    /**
     * Adds Pair to store
     * @param Pair $data
     */
    private function pushPair(Pair $data)
    {
        $this->_store[$data->getKey()] = $data->getValue();
    }

    /**
     * Adds array to store
     * @param array $data
     */
    private function pushArray(array $data)
    {
        foreach($data as $key=>$value){
            $this->pushPair(new Pair($key, $value));
        }
    }

    /**
     * Adds object to store
     * @param \Traversable $data
     */
    private function pushObject(\Traversable $data)
    {
        foreach($data as $key=>$value){
            if ($value instanceof \SimpleXMLElement){

                if (!empty(trim(preg_replace('/\s\s+/', '', (string)$value)))){
                    $this->pushPair(new Pair($key, (string)$value));
                }else{
                    $this->pushPair(new Pair($key, $value));
                }
            }else{
                $this->pushPair(new Pair($key, $value));
            }

        }
    }

    /**
     * Adds xml file to store
     * @param $data path to xml file
     */
    private function pushXmlFile($data)
    {
        $xml = simplexml_load_file($data);
        $this->pushObject($xml);

    }

    /**
     * Adds xml file to store
     * @param $filepath path to ini file
     */
    private function pushIniFile($filepath)
    {
        $data = parse_ini_file($filepath, false, INI_SCANNER_RAW);
        $this->pushArray($data);
    }

    /**
     * unset data from store
     * @param $key
     */
    public function delete($key)
    {
        unset($this->_store[$key]);
    }

    /**
     * Dump store to given format
     * @param string $format
     * @return array|string
     * @throws FormatNotSupportedException
     * @throws WrongTypeException
     */
    public function dump($format='array')
    {
        switch(mb_strtolower($format)){
            case 'array':
                return $this->dumpAsArray();
                break;
            case 'ini':
                return $this->dumpAsIni();
                break;
            case 'xml':
                return $this->dumpAsXml();
                break;
            case 'json':
                return $this->dumpAsJson();
                break;
            default:
                throw new FormatNotSupportedException;
        }
    }

    /**
     * @return array
     */
    private function dumpAsArray(){
            return $this->_store;
    }

    /**
     * @return string
     * @throws WrongTypeException
     */
    private function dumpAsIni()
    {
        $data = $this->dumpAsArray();
        $out = '';
        foreach ($data as $key=>$value){
            if (is_object($value)){
                throw new WrongTypeException('Cannot export object to ini yet.');
            }
            $out .= "$key=$value" . PHP_EOL;
        }
        return $out;
    }

    /**
     * @return string
     * @throws WrongTypeException
     */
    private function dumpAsXml()
    {
        $data = $this->dumpAsArray();
        $doc = new \DOMDocument('1.0');
        $doc->formatOutput = true;
        $root = $doc->createElement('root');
        $root = $doc->appendChild($root);
        foreach ($data as $key=>$value){
            if($value instanceof \SimpleXMLElement){
                $v = dom_import_simplexml($value);
                $dom_sxe = $doc->importNode($v, true);
                $element = $root->appendChild($dom_sxe);
            } elseif (is_object($value)){
                throw new WrongTypeException('Cannot export object to xml yet.');
            } else {
                $root->appendChild(new \DOMElement($key,$value));
            }
        }
        return $doc->saveXML();
    }

    /**
     * @return string
     */
    private function dumpAsJson()
    {
        $data = $this->dumpAsArray();
        return json_encode($data);
    }

    //Countable
    /**
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->_store);
    }

    //Iterator
    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return current($this->_store);
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->_position++;
        next($this->_store);
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return key($this->_store);
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return $this->_position<count($this->_store);
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->_position = 0;
        reset($this->_store);
    }

    //ArrayAccess
    /**
    * Whether a offset exists
    * @link http://php.net/manual/en/arrayaccess.offsetexists.php
    * @param mixed $offset <p>
    * An offset to check for.
    * </p>
    * @return boolean true on success or false on failure.
    * </p>
    * <p>
    * The return value will be casted to boolean if non-boolean was returned.
    * @since 5.0.0
    */
    public function offsetExists($offset)
    {
        return isset($this->_store[$offset]);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->get($offset) : null;
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->push(new Pair($offset, $value));
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        $this->delete($offset);
    }
}