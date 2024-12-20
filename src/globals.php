<?php

if (!function_exists('twig_to_array')) {
  function twig_to_array($seq, $preserveKeys = true) {
    if ($seq instanceof \Traversable) {
      return iterator_to_array($seq, $preserveKeys);
    }

    if (!\is_array($seq)) {
      return $seq;
    }

    return $preserveKeys ? $seq : array_values($seq);
  }
}
