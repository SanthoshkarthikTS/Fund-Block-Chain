<?php

class BlockController {
   
   private $_index;    
   private $_timestamp;    
   public $_data;    
   private $_previousHash;    
   public $_hash;    
   

   public function __construct($index,$timestamp,$data,$previousHash='')
   {
       $this->index = $index;
       $this->timestamp = $timestamp;
       $this->data = $data;
       $this->previousHash = $previousHash;
       $this->hash = $this->calculateHash();
   }
   
   public function calculateHash()
   {
       $hashInput = json_encode($this->data).$this->index.$this->timestamp.$this->previousHash;
       return (string)hash("sha256", $hashInput);
   }
}

?>