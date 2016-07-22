<?php

class IO
{
  private $clearCommand;

  /**
   * IO constructor.
   */
  public function __construct() {

    // Figure out which OS we're running.
    switch (true) {
      case stristr(PHP_OS, 'DAR'):
      case stristr(PHP_OS, 'LINUX'):
        $this->clearCommand = 'clear';
        break;
      case stristr(PHP_OS, 'WIN'):
        $this->clearCommand = 'cls';
        break;
      default :
        $this->clearCommand = 'cls';
        break;
    }

    // Since it's first (and only time we construct), clear the screen.
    $this->clear();
  }

  /**
   * Writes a simple message to the screen.
   *
   * @param string $message Message that will be output to the player.
   * @param bool $eol Should a new line be appended to the message.
   */
  public function output(string $message = '', bool $eol = true) {
    echo $message;
    if($eol === true) {
      echo PHP_EOL;
    }
  }

  /**
   * Asks for input from the player.
   *
   * @param string $message Input message that we should ask the player.
   * @param int $filter What filter_var filter we should use on the input.
   *
   * @return string Trimmed and filtered input from the player.
   */
  public function input(string $message, $filter = FILTER_SANITIZE_STRING): string {
    echo $message, PHP_EOL;
    $line = filter_var(trim(fgets(STDIN)), $filter);
    return $line;
  }

  /**
   * Checks whether the player agrees with / confirms a question / choice.
   *
   * @param string $message Input message that we should ask the player.
   * @param bool $default Default value of agreement.
   *
   * @return bool Did the player agree or not?
   */
  public function agree(string $message, bool $default = true): bool {

    // Get the input first.
    $input = $this->input($message, FILTER_SANITIZE_STRING);

    // If input was empty return default value.
    if(empty($input)) {
      return $default;
    }

    // Check if the first letter of lower cased input is a 'y', if so then this is true.
    if(strtolower($input[0]) === 'y') {
      return true;
    }
    elseif(strtolower($input[0]) === 'n') {
      return false;
    }
    else {
      // The input is something it shouldn't be? Return default.
      return $default;
    }
  }

  /**
   * Clears the screen using different system command depending on the OS. Hopefully!
   */
  public function clear() {
    system($this->clearCommand);
  }

}

class Game
{
  public $gameOver = false;

  public $spentAnimals = 0;
  public $spentFood = 0;
  public $spentAmmunition = 0;
  public $spentClothing = 0;
  public $spentMisc = 0;

  public $startDate = '1847-04-12 12:00:00';
  public $daysInTurn = 14;
  public $turnAmount = 16;
  public $dates = [];

  public $turnCurrent = 1;

  public function __construct() {

    // Work out the dates in nice text form for the turns.
    $dateInterval = new DateInterval('P' . $this->daysInTurn . 'D');

    // America/Chicago should be same as Independence, Missouri (CT).
    $dateTime = new DateTime($this->startDate, new DateTimeZone('America/Chicago'));
    $this->dates[] = $dateTime->format('F j Y');
    for($i = 0; $i < $this->turnAmount; $i++) {
      $this->dates[] = $dateTime->add($dateInterval)->format('F j');
    }
  }

  public function advanceTurn() {
    $this->turnCurrent++;

    // Check if the game has ended.
    if($this->turnCurrent > $this->turnAmount) {
      $this->gameOver = true;
    }
  }

  public function doShopping($animals, $food, $ammunition, $clothing, $misc) {

    // The amounts that the player has spent.
    $this->spentAnimals = $animals;
    $this->spentFood = $food;
    $this->spentAmmunition = $ammunition;
    $this->spentClothing = $clothing;
    $this->spentMisc = $misc;
  }
}

// Initiate our classes.
$io = new IO;

// Does the player need instructions?
if($io->agree('Do you need instructions?')) {
  $io->clear();
  $io->output('This program simulates a trip over the Oregon Trail from');
  $io->output('Independence, Missouri to Oregon City, Oregon in 1847.');
  $io->output('Your family of five will cover the 2,000 mile Oregon Trail');
  $io->output('in 5-6 months --- If you make it alive.');
  $io->output();
  $io->output('You had saved $900 to spend for the trip, and you\'ve just');
  $io->output('   paid $200 for a wagon.');
  $io->output('You will need to spend the rest of your money on the');
  $io->output('   following items:');
  $io->output();
  $io->output('     Oxen - You can spend $200-$300 on your team.');
  $io->output('            The more you spend, the faster you\'ll go');
  $io->output('            because you you\'ll have better animals');
  $io->output();
  $io->output('     Food - The more you have, the less chance there');
  $io->output('            is of getting sick.');
  $io->output();
  $io->output('     Ammunition - $1 buys a belt of 50 bullets');
  $io->output('            You will need bullets for attacks by animals');
  $io->output('            and bandits, and for hunting food.');
  $io->output();
  $io->output('     Clothing - This is especially important for the cold');
  $io->output('            weather you will encounter when crossing');
  $io->output('            the mountains.');
  $io->output();
  $io->output('     Miscellaneous Supplies - This includes medicine and');
  $io->output('            other things you will need for sickness');
  $io->output('            and emergency repairs.');
  $io->output();
  $io->output('You can spend all your money before you start your trip -');
  $io->output('or you can save some of your cash to spend at forts along');
  $io->output('the way when you run low. However, items cost more at');
  $io->output('the forts. You can also go hunting along the way to get');
  $io->output('more food.');
  $io->output('Whenever you have to use your trusty rifle along the way,');
  $io->output('you will see the words: TYPE BANG. The faster you type');
  $io->output('in the word "BANG" and hit the "RETURN" key, the better');
  $io->output('luck you\'ll have with your gun.');
  $io->output();
  $io->output('GOOD LUCK!!!');
}

// Initiate new game.
$game = new Game();

// The main game loop.
while($game->gameOver === false) {

  // Output game date at the start of the turn.
  $io->output($game->dates[$game->turnCurrent]);

  // If this is first turn we need to do some shopping first.
  if($game->turnCurrent === 1) {
    $animals = $io->input('How much do you want to spend on your oxen team?', FILTER_SANITIZE_NUMBER_INT);
    $food = $io->input('How much do you want to spend on food?', FILTER_SANITIZE_NUMBER_INT);
    $ammunition = $io->input('How much do you want to spend on ammunition?', FILTER_SANITIZE_NUMBER_INT);
    $clothing = $io->input('How much do you want to spend on clothing?', FILTER_SANITIZE_NUMBER_INT);
    $misc = $io->input('How much do you want to spend on miscellaneous items?', FILTER_SANITIZE_NUMBER_INT);

    // Validate the amounts.


    // Pass this information along and save it in the game state.
    $game->doShopping($animals, $food, $ammunition, $clothing, $misc);
  }

  // Increase the turn counter by one at the end of the turn.
  $io->output($game->turnCurrent);
  $game->advanceTurn();
}
