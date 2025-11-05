class Participant {
  final int id;
  final String firstName;
  final String lastName;
  final String email;
  final String? phone;
  final String? company;
  final String? position;
  final String qrCode;
  final String status;
  final DateTime createdAt;
  final bool isCheckedIn;

  Participant({
    required this.id,
    required this.firstName,
    required this.lastName,
    required this.email,
    this.phone,
    this.company,
    this.position,
    required this.qrCode,
    required this.status,
    required this.createdAt,
    required this.isCheckedIn,
  });

  String get fullName => '$firstName $lastName';

  factory Participant.fromJson(Map<String, dynamic> json) {
    return Participant(
      id: json['id'],
      firstName: json['firstName'],
      lastName: json['lastName'],
      email: json['email'],
      phone: json['phone'],
      company: json['company'],
      position: json['position'],
      qrCode: json['qrCode'],
      status: json['status'],
      createdAt: DateTime.parse(json['createdAt']),
      isCheckedIn: json['isCheckedIn'] ?? false,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'firstName': firstName,
      'lastName': lastName,
      'email': email,
      'phone': phone,
      'company': company,
      'position': position,
      'qrCode': qrCode,
      'status': status,
      'createdAt': createdAt.toIso8601String(),
      'isCheckedIn': isCheckedIn,
    };
  }
}