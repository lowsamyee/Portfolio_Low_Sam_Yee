/*The Piece class serves as an abstract base class for all chess pieces, 
 providing common properties and methods. (INHERITANCE)

Encapsulation: Encapsulates the properties of the chess piece and allows controllers to access the piece.

Written by: Hui May */

package model;

import java.awt.Image;
import java.util.ArrayList;

import java.awt.image.BufferedImage;
import javax.imageio.ImageIO;
import javax.swing.ImageIcon;

public abstract class Piece {
    private String name;
    private String color;
    private ImageIcon image;
    private boolean isFlipped = false;
    private int x, y;

    public Piece(String name, String color, int x, int y) {
        this.name = name;
        this.color = color;
        this.x = x;
        this.y = y;
        this.image = loadImage();
    }

    private ImageIcon loadImage() {
        // Construct the image path dynamically
        String imagePath = "/resources/image/" + name + color + ".png"; // Adjust path as needed
        try {
            ImageIcon icon = new ImageIcon(getClass().getResource(imagePath));
            Image img = icon.getImage(); // Get the original image

            // Get the size of the button
            int cellSize = 45; // Set a smaller size than cell that defined in boardview (50px)

            // Resize the image to fit the button size while maintaining aspect ratio
            Image scaledImg = img.getScaledInstance(cellSize, cellSize, Image.SCALE_SMOOTH);
            return new ImageIcon(scaledImg); // Return the resized image as an ImageIcon
        } catch (Exception e) {
            System.err.println("Image not found: " + imagePath);
            return null; // Return null or a placeholder if the image is not found
        }
    }

    // Encapsulation, Getters
    public String getName() {
        return name;
    }

    // Encapsulation, Getters
    public String getColor() {
        return color;
    }

    // Encapsulation, Getters
    public String getNameNColour() {
        return name + " (" + color + ")";
    }

    // Encapsulation, Getters
    public ImageIcon getImage() {
        return image;
    }

    // Encapsulation, Getters
    public int getX() {
        return x;
    }

    // Encapsulation, Getters
    public int getY() {
        return y;
    }

    // Encapsulation, Setters
    public void setX(int newX) {
        x = newX;
    }

    // Encapsulation, Setters
    public void setY(int newY) {
        y = newY;
    }

    // Encapsulation, Getters
    public boolean getFlipped() {
        return isFlipped;
    }

    public void rotateImage() {
        try {
            String imagePath = "/resources/image/" + name + color + ".png";
            BufferedImage originalImage = ImageIO.read(getClass().getResourceAsStream(imagePath));
            int width = originalImage.getWidth();
            int height = originalImage.getHeight();

            BufferedImage flippedImage = new BufferedImage(width, height, originalImage.getType());
            if (!isFlipped) {
                for (int y = 0; y < height; y++) {
                    for (int x = 0; x < width; x++) {
                        flippedImage.setRGB(x, height - 1 - y, originalImage.getRGB(x, y));
                    }
                }
            } else { // Use the original image (unflipped)
                flippedImage = originalImage;
            }

            // Resize the flipped image to fit the button size
            int cellSize = 45; // Match the size defined in loadImage
            Image scaledFlippedImage = flippedImage.getScaledInstance(cellSize, cellSize, Image.SCALE_SMOOTH);

            this.image = new ImageIcon(scaledFlippedImage);
            isFlipped = !isFlipped; // Toggle the state
        } catch (Exception e) {
            System.err.println("Error flipping image: " + e.getMessage());
        }
    }

    public boolean skipOver(int newX, int newY, Board board) {
        // Check if the new position is within bounds
        if (!board.isWithinBounds(newX, newY)) {
            return false;
        }

        // Get the piece at the new position
        Piece pieceAtNewPos = board.getPiece(newX, newY);

        // If there is no piece at the new position, or it's an opponent's piece, the
        // move is allowed
        if (pieceAtNewPos == null || !pieceAtNewPos.getColor().equals(this.getColor())) {
            return true;
        }

        // If there's a piece of the same color, cannot move (skip over it)
        return false;
    }

    // polymorphism - only modify the getAvailableMoves method for different piece
    public abstract ArrayList<int[]> getAvailableMoves(int x, int y, Board board);

}