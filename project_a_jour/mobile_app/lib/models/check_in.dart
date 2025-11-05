import 'participant.dart';

//ity dia manova ny vaminy json iny ho lasa objet dart 
class CheckIn {
  final int id;
  final DateTime checkedInAt;
  final String? checkedInBy;
  final String? notes;
  final Participant participant;

  CheckIn({
    required this.id,
    required this.checkedInAt,
    this.checkedInBy,
    this.notes,
    required this.participant,
  });

  factory CheckIn.fromJson(Map<String, dynamic> json) {
    return CheckIn(
      id: json['id'],
      checkedInAt: DateTime.parse(json['checkedInAt']),
      checkedInBy: json['checkedInBy'],
      notes: json['notes'],
      participant: Participant.fromJson(json['participant']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'checkedInAt': checkedInAt.toIso8601String(),
      'checkedInBy': checkedInBy,
      'notes': notes,
      'participant': participant.toJson(),
    };
  }
}