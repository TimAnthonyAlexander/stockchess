function doThis(i, j, piece, field) {
    let castles = false;

    // Add the move to the form
    // 14 = e2
    // 47 = g5
    // Convert.
    let a = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h'];
    let b = ['1', '2', '3', '4', '5', '6', '7', '8'];
    // Convert i and j to a  position
    let pos = a[j] + b[i];

    let moveInput = document.getElementById('move');

    if ((piece === 'k' || piece === 'K') && a[j] === 'e') {
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
        if (moveInput.value === 'K' && (a[j] === 'g' || a[j] === 'h' || a[j] === 'c' || a[j] === 'b' || a[j] === 'a') && (b[i] === '1' || b[i] === '8') && castles) {
            if (a[j] === 'g' || a[j] === 'h') {
                moveInput.value = 'O-O';
            } else if(a[j] === 'c' || a[j] === 'b' || a[j] === 'a') {
                moveInput.value = 'O-O-O';
            }
        } else if (moveInput.value.length === 1 && moveInput.value === moveInput.value.toLowerCase()) {
            if (piece === '.') {
                moveInput.value = '';
            } else {
                moveInput.value += 'x';
            }
            moveInput.value += pos;
        } else {
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
