<?php

	/*!
	 *		This class can be used to traverse an arbitrary array, and simplifies the creation of standalone template functions that
	 *		rely on database data or array-driven data.	
	 */
	class WordTwitArrayIterator {
		var $array;		//! The array the iterator is meant to traverse
		var $cur_pos;	//! The current position in the iterator, 0 based
		var $count;		//! A count of all the items in the array
		var $cur_key;	//! The array key for the current item
		
		function WordTwitArrayIterator( $a ) {
			$this->array = $a;
			$this->cur_pos = 0;
			$this->count = count( $a );
			$this->cur_key = false;
		}	
		
		/*! 	\brief Used to reset the array iterator back to its initial position		 
		 *
		 *		This method resets the array iterator back to its initial position.  It should be called prior to any attempt to traverse the array a second time. 
		 */	
		function rewind() {
			$this->cur_pos = 0;
		}
	
		/*! 	\brief Checks to see whether there are any items available to traverse		 
		 *
		 *		This method checks to see if there are any items in the array that can be traversed.
		 *
		 *		\returns True if there are elements in the array, false if there are not.
		 *
		 *		\example array_iterator_loop.php 
		 *		This is an example of typical usage of this class:
		 */			
		function have_items() {
			$has_items = ( $this->cur_pos < $this->count );	
			if ( !$has_items ) {
				// force a reset after returning false
				$this->cur_pos = 0;	
				@reset( $this->array );
			}
			
			return $has_items;
		}

		/*! 	\brief Returns the next available item in the array		 
		 *
		 *		This method returns the next available item in the array.  This method is meant to be called in a loop using have_items().
		 *
		 *		\returns The next available item		 
		 */				
		function the_item() {
			if ( $this->cur_pos == 0 ) {
				$item = current( $this->array );
				$this->cur_key = key( $this->array );
			} else { 			
				$item = next( $this->array );	
				$this->cur_key = key( $this->array );
			}
			
			$this->cur_pos++;
			
			return $item;	
		}

		/*! 	\brief Returns the current array traversal position.		 
		 *
		 *		This method returns current array traversal position.		 
		 *
		 *		\returns The position in the array traversal.  This value is 0 immediately after object creation, or after calling rewind().		 
		 */				
		function current_position() {
			return $this->cur_pos;	
		}

		/*! 	\brief Returns the array key for the current item		 
		 *
		 *		This method returns the array key for the current item.	 
		 *
		 *		\returns The array key of the current item		 
		 */			
		function the_key() {
			return $this->cur_key;	
		}
	}