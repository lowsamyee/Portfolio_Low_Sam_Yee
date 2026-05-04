//The Sau class represents the "Sau" chess piece, capable of moving one step in any direction.
//Written by: Hui May

package model;

import java.util.ArrayList;

public class Sau extends Piece {

    public Sau(String colorTurn, int x, int y) {
        super("Sau", colorTurn, x, y); 
    }

    //Calculates all valid one-step moves in any direction for the Sau piece.
    @Override
    public ArrayList<int[]> getAvailableMoves(int x, int y, Board board) {
        ArrayList<int[]> availableMoves = new ArrayList<>();

        // All possible one-step moves in any direction
        int[][] directions = {
                { 1, 0 }, // Down
                { -1, 0 }, // Up
                { 0, 1 }, // Right
                { 0, -1 }, // Left
                { 1, 1 }, // Down-right
                { 1, -1 }, // Down-left
                { -1, 1 }, // Up-right
                { -1, -1 } // Up-left
        };

        for (int[] direction : directions) {
            int newX = x + direction[0];
            int newY = y + direction[1];

            // Check if the move is within bounds
            if (board.isWithinBounds(newX, newY)) {
                Piece pieceAtCell = board.getPiece(newX, newY);

                // Check if the destination is empty or occupied by an opponent's piece
                if (pieceAtCell == null || !pieceAtCell.getColor().equalsIgnoreCase(this.getColor())) {
                    availableMoves.add(new int[] { newX, newY });
                }
            }
        }

        return availableMoves;
    }

}
