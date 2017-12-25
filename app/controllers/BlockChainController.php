<?php

class BlockChainController extends BaseController {
   
    const CURRENTBLOCK = '';
    const PREVIOUSBLOCK = '';
    public function __construct() {
       $this->chain = [$this->createGenesisBlock()];
    }

   public function createGenesisBlock() {

       return new BlockController(0, '01/01/2017', array("name" => "Genesis block"), "0");
   }

   public function getLatestBlock() {
       return $this->chain[sizeof($this->chain) - 1];
   }

   public function addBlock($newBlock) {
       $newBlock->previousHash = $this->getLatestBlock()->hash;
     
       $newBlock->hash = $newBlock->calculateHash();
       array_push($this->chain, $newBlock);
 
   }

   public function isChainValid() {
       for ($i = 1; $i < sizeof($this->chain); $i++){
           $this->CURRENTBLOCK = $this->chain[$i];
           $this->PREVIOUSBLOCK = $this->chain[$i - 1];

           if ( $this->CURRENTBLOCK->hash !==  $this->CURRENTBLOCK->calculateHash()) {
               return false;
           }

           if ( $this->CURRENTBLOCK->previousHash !==  $this->PREVIOUSBLOCK->hash) {
               return false;
           }
       }
       return true;
   }
}