function generateRandomCode() {
    $('#renter-verify_code').val( getRandomInt(10000000, 99999999) );
}

function getRandomInt(min, max) {
    min = Math.ceil(min);
    max = Math.floor(max);
    return Math.floor(Math.random() * (max - min)) + min; //Максимум не включается, минимум включается
  }