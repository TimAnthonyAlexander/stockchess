let castles = false;
function doThis(i, j, piece, field) {

    // Add the move to the form
    // 14 = e2
    // 47 = g5
    // Convert.
    let a = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h'];
    let b = ['1', '2', '3', '4', '5', '6', '7', '8'];
    // Convert i and j to a  position
    let pos = a[j] + b[i];

    let moveInput = document.getElementById('move');

    if (moveInput.value === '' && (piece === 'k' || piece === 'K') && 'e' === a[j]) {
        console.log('Activated castles');
        castles = true;
    }

    // If the field is empty, then add either the piece or if it is a pawn, then add the from position
    if (moveInput.value === '') {
        if (piece.search(/[prnbkqPRNBKQ]/) !== -1) {
            document.getElementById(field).style.backgroundColor = '#34bee9';

            // if piece is pawn, add i to form
            if (piece.search(/[pP]/) !== -1) {
                moveInput.value = a[j];
            } else {
                moveInput.value = piece.toUpperCase();
            }
        }
    } else {
        // If the field is not empty, then add the to position

        // If input length === 1, then it is a pawn. If piece is null, remove input. Else, add x
        // If input is lowercase, then it is a pawn.
        console.log(moveInput.value);
        if (moveInput.value === 'K' && (a[j] === 'g' || a[j] === 'h' || a[j] === 'c' || a[j] === 'b' || a[j] === 'a') && (b[i] === '1' || b[i] === '8') && castles) {
            console.log('Castles actually');
            if (a[j] === 'g' || a[j] === 'h') {
                console.log('Castles kingside');
                moveInput.value = 'O-O';
            } else if(a[j] === 'c' || a[j] === 'b' || a[j] === 'a') {
                console.log('Castles queenside');
                moveInput.value = 'O-O-O';
            }
        } else if (moveInput.value.length === 1 && moveInput.value === moveInput.value.toLowerCase()) {
            console.log('Pawn');
            if (piece === '.') {
                moveInput.value = '';
            } else {
                moveInput.value += 'x';
            }
            moveInput.value += pos;
        } else {
            console.log('Not pawn');
            if (piece !== '.') {
                moveInput.value += 'x';
            }
            moveInput.value += pos;
        }



        // Play the move.mp3 sound
        let audio = new Audio('move.mp3');
        audio.play();


        setTimeout(function() {
            document.getElementById('form').submit();
        }, 500);
    }
}
