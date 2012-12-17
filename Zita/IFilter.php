<?php
namespace Zita;

/**
 * All the filters to be used in @Filter, @OutputFilter, @InputFilter should derive from this class or utulity classes
 * OutputFilter or InputFilter.
 *
 * Derived classes' names should end with Filter, such as HTMLMinifyFilter.
 */
interface IFilter extends  IAnnotation
{
}
