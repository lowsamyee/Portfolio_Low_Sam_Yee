//The Xor class represents the "Xor" chess piece, 
//capable of moving diagonally across the board.
//Written by: Kah Wei

package model;

import java.util.ArrayList;

public class Xor extends Piece {

    public Xor(String colorTurn, int x, int y) {
        super("Xor", colorTurn, x, y); 
    }

    @Override
    public ArrayList<int[]> getAvailableMoves(int x, int y, Board board) {
        ArrayList<int[]> availableMoves = new ArrayList<>();
        int[][] directions = {
                { -1, -1 }, // Up-left
                { -1, 1 }, // Up-right
                { 1, -1 }, // Down-left
                { 1, 1 } // Down-right
        };

        int currentX = this.getX(); // Current piece X-coordinate
        int currentY = this.getY(); // Current piece Y-coordinate

        // Iterate through all diagonal directions
        for (int[] direction : directions) {
            int dx = direction[0];
            int dy = direction[1];

            int newX = currentX + dx;
            int newY = currentY + dy;

            // Traverse in the current diagonal direction until blocked
            while (board.isWithinBounds(newX, newY)) {
                ;

                if (board.isWithinBounds(newX, newY)) {
                    Piece pieceAtCell = board.getPiece(newX, newY);
                    // Cell is empty, add to available moves
                    if (pieceAtCell == null || !pieceAtCell.getColor().equals(this.getColor())) {
                        availableMoves.add(new int[] { newX, newY });
                    }
                    if (pieceAtCell != null)
                        break; // Stop if the piece is not empty
                } else {
                    // Cell is occupied, stop further movement in this direction
                    break;
                }

                // Move further in the same direction
                newX += dx;
                newY += dy;
            }
        }

        return availableMoves;
    }

}
