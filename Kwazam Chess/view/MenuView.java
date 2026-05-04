/*
 * The MenuView class represents the main menu interface of the game,
 * providing options to start the game and toggle sound settings.
 * 
 * Design Pattern: Part of the MVC pattern, acting as a subcomponent of the View layer.
 * 
 * Composition:
 * - This class is part of the MainView and is directly managed by it.
 * - Contains a start button and a mute/unmute checkbox.
 * 
 * Delegation:
 * - Game starting logic is delegated to the MainView, which communicates with the GameController.
 * - Sound settings updates (mute/unmute) are also delegated to the MainView.
 * 
 * Written by: Hoo Enn Xin
 */
package view;

import javax.swing.*;
import java.awt.*;

public class MenuView extends JPanel {
    private JButton startGameButton;
    private JCheckBox muteCheckBox;

    public MenuView(MainView mainView) {
        this.setLayout(new GridBagLayout());
        GridBagConstraints gbc = new GridBagConstraints();

        startGameButton = new JButton("Start Game");

        // Adjust the size and font of the button
        startGameButton.setPreferredSize(new Dimension(150, 50));
        startGameButton.setFont(new Font("Arial", Font.BOLD, 15));

        startGameButton.addActionListener(e -> mainView.startGame());

        // Mute/Unmute Checkbox
        muteCheckBox = new JCheckBox("Mute Sound");
        muteCheckBox.setFont(new Font("Arial", Font.PLAIN, 14));

        // ActionListener to update mute status when checkbox is clicked
        muteCheckBox.addActionListener(e -> {
            boolean isMuted = muteCheckBox.isSelected(); // Get mute checkbox state
            mainView.setMute(isMuted); // Pass the mute status to MainView
        });

        // Configure GridBagConstraints for the button
        gbc.gridx = 0;
        gbc.gridy = 0;
        gbc.weightx = 1.0;
        gbc.weighty = 1.0;
        gbc.fill = GridBagConstraints.CENTER;
        gbc.insets = new Insets(20, 20, 20, 20);

        this.add(startGameButton, gbc);

        // Configure GridBagConstraints for the checkbox
        gbc.gridy = 1; // Move the checkbox below the start button
        this.add(muteCheckBox, gbc);
    }
}
