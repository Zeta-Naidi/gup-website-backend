<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Log;

class AdminMustBeLinkedToDistributor implements DataAwareRule, ValidationRule
{
  /**
   * All of the data under validation.
   *
   * @var array<string, mixed>
   */
  protected $data = [];

  /**
   * Run the validation rule.
   *
   * @param \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
   */
  public function validate(string $attribute, mixed $value, Closure $fail): void
  {
    if($value == 1 && $this->data->userRelationship != 'distributor')
      $fail("Can't create an admin user which is not linked to a distributor");
  }
  /**
   * Set the data under validation.
   *
   * @param  array<string, mixed>  $data
   */
  public function setData(array $data): static
  {
    $this->data = $data;

    return $this;
  }
}
